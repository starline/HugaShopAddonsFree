<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace HugaShop\Addons\CargoSort\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;

final class CargoSortController extends BaseAdminController
{
    use BaseAddonTrait;

    private string $import_file = 'cargosort.csv';
    private array $import_columns = ['tyre_size', 'name', 'size', 'cost', 'count', 'firstly_count'];
    private string $export_filename = 'cargosort.csv';
    private string $locale = 'ru_RU.UTF-8';
    private string $column_delimiter = ';';

    private float $total_size = 0;
    private float $total_cost = 0;
    private float $box_size = 0;
    private float $box_cost = 0;
    private ?float $box_delivery_cost = null;

    #[Route('/CargoSort', name: 'AddonCargoSort', priority: 20)]
    public function index()
    {
        Design::assign('addon', $this->getAddon());

        if (!is_writable(Config::get('import_files_dir'))) {
            Design::assign('message_error', 'no_permission');
        }

        $old_locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, $this->locale);

        if (setlocale(LC_ALL, 0) !== $this->locale) {
            Design::assign('message_error', 'locale_error');
            Design::assign('locale', $this->locale);
        }

        setlocale(LC_ALL, $old_locale);

        if (Secure::checkCSRF() && Request::files('file')) {
            $this->box_size = Request::post('box_size');
            $this->box_cost = Request::post('box_cost');
            $this->box_delivery_cost = Request::post('box_delivery_cost');

            if ($this->box_size > 0 && $this->box_cost > 0) {
                $uploaded_name = Request::files('file', 'tmp_name');
                $temp = tempnam(Config::get('import_files_dir'), 'temp_');

                if (!move_uploaded_file($uploaded_name, $temp)) {
                    Design::assign('message_error', 'upload_error');
                }

                if (!$this->convertFile($temp, Config::get('import_files_dir') . $this->import_file)) {
                    Design::assign('message_error', 'convert_error');
                } else {
                    Design::assign('filename', Request::files('file', 'name'));
                }

                unlink($temp);

                $f = fopen(Config::get('import_files_dir') . $this->import_file, 'r');
                $products = [];

                while (($line = fgetcsv($f, 0, $this->column_delimiter)) !== false) {
                    $product = [];
                    if (is_array($line)) {
                        foreach ($this->import_columns as $i => $col) {
                            if (isset($line[$i]) && $line[$i] !== '' && $col !== '') {
                                $product[$col] = $line[$i];
                            }
                        }
                        $product['size'] = (float) str_replace(',', '.', trim($product['size']));
                        $product['cost'] = (float) str_replace(',', '.', trim($product['cost']));
                        $temp_firstly_count = $product['firstly_count'] ?? 0;
                        for ($i = 0; $i < $product['count']; $i++) {
                            $product['firstly'] = 2;
                            if ($temp_firstly_count > 0) {
                                $temp_firstly_count--;
                                $product['firstly'] = 1;
                            }
                            $products[] = $product;
                            $this->total_size += $product['size'];
                            $this->total_cost += $product['cost'];
                        }
                    }
                }
                fclose($f);

                $ideal_box_count_of_size = $this->total_size / $this->box_size;
                $ideal_box_count_of_cost = $this->total_cost / $this->box_cost;

                $temp_size = [];
                $temp_firstly = [];
                foreach ($products as $k => $p) {
                    $temp_size[$k] = $p['size'];
                    $temp_firstly[$k] = $p['firstly'];
                }
                array_multisort($temp_firstly, SORT_ASC, SORT_NUMERIC, $temp_size, SORT_DESC, SORT_NUMERIC, $products);

                if (is_writable(Config::get('export_files_dir') . $this->export_filename)) {
                    unlink(Config::get('export_files_dir') . $this->export_filename);
                }
                $export_f = fopen(Config::get('export_files_dir') . $this->export_filename, 'ab');

                $boxes_array = [];
                $number_of_box = 1;
                $sort_products = [];
                $temp_products_arr = $products;

                while (count($temp_products_arr) > 0) {
                    $temp_size = 0;
                    $temp_cost = 0;
                    $box_array = [];

                    foreach ($temp_products_arr as $n => $p) {
                        if ($p['cost'] > $this->box_cost) {
                            Design::assign('message_error', 'Цена товара больше стоимости контейнера');
                            fclose($export_f);
                            return $this->fetchAddonResponse('index.tpl');
                        }
                        if (($temp_size + $p['size']) < $this->box_size && ($temp_cost + $p['cost']) < $this->box_cost) {
                            $temp_size += $p['size'];
                            $temp_cost += $p['cost'];
                            $p['number_of_box'] = $number_of_box;
                            unset($p['count'], $p['firstly_count']);
                            $sort_products[] = $p;
                            end($sort_products);
                            $box_array[key($sort_products)] = $p;
                            $p['size'] = (string) str_replace('.', ',', $p['size']);
                            $p['cost'] = (string) str_replace('.', ',', $p['cost']);
                            fputcsv($export_f, $p, $this->column_delimiter);
                            unset($temp_products_arr[$n]);
                        }
                    }

                    if (isset($this->box_delivery_cost)) {
                        $product_in_box_count = count($box_array);
                        $delivery_product_in_box_cost = $this->box_delivery_cost / $product_in_box_count;
                        foreach ($box_array as $key => $value) {
                            $sort_products[$key]['delivery_product_in_box_cost'] = $delivery_product_in_box_cost;
                        }
                    }

                    $boxes_array[$number_of_box] = $box_array;
                    $number_of_box++;
                }

                if (isset($this->box_delivery_cost)) {
                    $temp_products_arr = $sort_products;
                    while (count($temp_products_arr) > 0) {
                        $article_array = [];
                        $temp_product = array_shift($temp_products_arr);
                        $article_array[] = $temp_product;
                        $all_delivery_cost = $temp_product['delivery_product_in_box_cost'];
                        foreach ($temp_products_arr as $key => $p) {
                            if ($p['name'] == $temp_product['name']) {
                                $article_array[] = $p;
                                $all_delivery_cost += $p['delivery_product_in_box_cost'];
                                unset($temp_products_arr[$key]);
                            }
                        }
                        $middle_delivery_cost = $all_delivery_cost / count($article_array);
                        foreach ($sort_products as $key => $p) {
                            if ($p['name'] == $temp_product['name']) {
                                $sort_products[$key]['middle_delivery_cost'] = $middle_delivery_cost;
                            }
                        }
                    }
                }

                fclose($export_f);

                Design::assign('box_count', $number_of_box - 1);
                Design::assign('products', $sort_products);
                Design::assign('total_size', $this->total_size);
                Design::assign('total_cost', $this->total_cost);
                Design::assign('ideal_box_count_of_size', $ideal_box_count_of_size);
                Design::assign('ideal_box_count_of_cost', $ideal_box_count_of_cost);
                Design::assign('box_size', $this->box_size);
                Design::assign('box_cost', $this->box_cost);
                Design::assign('box_delivery_cost', $this->box_delivery_cost);
            } else {
                Design::assign('message_error', 'Установите размер и стоимость груза контейнера');
            }
        }

        return $this->fetchAddonResponse('index.tpl');
    }

    private function convertFile(string $source, string $dest): bool
    {
        $teststring = file_get_contents($source);
        if (preg_match('//u', $teststring)) {
            return copy($source, $dest);
        }
        if (!$src = fopen($source, 'r')) {
            return false;
        }
        if (!$dst = fopen($dest, 'w')) {
            return false;
        }
        while (($line = fgets($src, 4096)) !== false) {
            $line = $this->winToUtf($line);
            fwrite($dst, $line);
        }
        fclose($src);
        fclose($dst);
        return true;
    }

    private function winToUtf(string $text): string
    {
        if (function_exists('iconv')) {
            return (string) @iconv('windows-1251', 'UTF-8', $text);
        }
        $t = '';
        for ($i = 0, $m = strlen($text); $i < $m; $i++) {
            $c = ord($text[$i]);
            if ($c <= 127) {
                $t .= chr($c);
                continue;
            }
            if ($c >= 192 && $c <= 207) {
                $t .= chr(208) . chr($c - 48);
                continue;
            }
            if ($c >= 208 && $c <= 239) {
                $t .= chr(208) . chr($c - 48);
                continue;
            }
            if ($c >= 240 && $c <= 255) {
                $t .= chr(209) . chr($c - 112);
                continue;
            }
            if ($c == 184) {
                $t .= chr(209) . chr(145);
                continue;
            }
            if ($c == 168) {
                $t .= chr(208) . chr(129);
                continue;
            }
            if ($c == 179) {
                $t .= chr(209) . chr(150);
                continue;
            }
            if ($c == 178) {
                $t .= chr(208) . chr(134);
                continue;
            }
            if ($c == 191) {
                $t .= chr(209) . chr(151);
                continue;
            }
            if ($c == 175) {
                $t .= chr(208) . chr(135);
                continue;
            }
            if ($c == 186) {
                $t .= chr(209) . chr(148);
                continue;
            }
            if ($c == 170) {
                $t .= chr(208) . chr(132);
                continue;
            }
            if ($c == 180) {
                $t .= chr(210) . chr(145);
                continue;
            }
            if ($c == 165) {
                $t .= chr(210) . chr(144);
                continue;
            }
            if ($c == 184) {
                $t .= chr(209) . chr(145);
                continue;
            }
        }
        return $t;
    }
}
