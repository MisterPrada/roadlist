<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MainController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function getRoadList(Request $request)
    {
        $month_rus = [
            'Января',
            'Февраля',
            'Марта',
            'Апреля',
            'Мая',
            'Июня',
            'Июля',
            'Августа',
            'Сентября',
            'Октября',
            'Ноября',
            'Декабря'
        ];

        $taxation_collect = collect();
        $taxation = []; // Таблица содержащая таксировку
        $register = []; // Таблица содержащая Регист
        $address = collect(); // Коллекция содержащая Адреса
        $customer_offset = 0;

        $spreadsheet_register = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->register); // Общий объект
        $activeSheet_register = $spreadsheet_register->getActiveSheet(); // Получаем вкладку

        // Заполним массив адресов
        for ($i = 11; $i < 10000; ++$i) {

            if (!$activeSheet_register->getCell('M' . $i)->getCalculatedValue()) {
                break;
            }

            $address->push($activeSheet_register->getCell('M' . $i)->getCalculatedValue());
        }

        // Заполним массив таксировок (правые  таблицы)
        for($table = 1; $table < 31; ++$table){

            $taxation = [];
            for ($i = (11 * $table) + (2 * ($table - 1)); $i < 10000; ++$i) {
                if (!$activeSheet_register->getCell('R' . $i)->getCalculatedValue()) {
                    break;
                }

                $taxation[$activeSheet_register->getCell('R' . $i)->getCalculatedValue()] = (object)[
                    'pp' => $activeSheet_register->getCell('R' . $i)->getCalculatedValue(),
                    'prb' => $activeSheet_register->getCell('S' . $i)->getFormattedValue(),
                    'ub' => $activeSheet_register->getCell('T' . $i)->getFormattedValue(),
                    'total' => $activeSheet_register->getCell('U' . $i)->getCalculatedValue(),
                    'gruz' => $activeSheet_register->getCell('V' . $i)->getCalculatedValue(),
                ];

            }

            $taxation_collect->put($table, $taxation); // Заносим n таблицу из правыз
        }

        // Заполним массив регистра
        for ($i = 11; $i < 10000; ++$i) {

            if (!$activeSheet_register->getCell('A' . $i)->getCalculatedValue()) {
                break;
            }

            $success = (int)$activeSheet_register->getCell('H' . $i)->getCalculatedValue();
            $first_tax = (int)$activeSheet_register->getCell('K' . $i)->getCalculatedValue();
            $tax_table = (int)$activeSheet_register->getCell('L' . $i)->getCalculatedValue();
            $price = (float)$activeSheet_register->getCell('N' . $i)->getCalculatedValue();

            $register[] = (object)[
                'date' => Carbon::create($activeSheet_register->getCell('A' . $i)->getFormattedValue())->format('d.m.yy'),
                'n1' => $activeSheet_register->getCell('B' . $i)->getCalculatedValue(),
                'n2' => $activeSheet_register->getCell('C' . $i)->getCalculatedValue(),
                'car_mark' => $activeSheet_register->getCell('D' . $i)->getFormattedValue(),
                'number' => $activeSheet_register->getCell('E' . $i)->getFormattedValue(),
                'road_count' => $activeSheet_register->getCell('F' . $i)->getCalculatedValue(),
                'volume' => $activeSheet_register->getCell('G' . $i)->getCalculatedValue(),
                'success' => $success,
                'first_tax' => $first_tax,
                'tax_table' => $tax_table,
                'price' => $price,
            ];


            $address_tmp = clone $address;
            $address = $address_tmp->splice($success); // Обрезаем

            $activeSheet_register->setCellValue('I' . $i, implode(",\r\n", $address_tmp->all())); // Выставляем адрес
        }

        dd($register);

        // Выравнивание строк по высоте относительно контента (для адресов)
        foreach ($activeSheet_register->getRowDimensions() as $rd) {
            $rd->setRowHeight(-1);
        }

        $temp_dir = public_path('temp/' . Str::uuid());
        File::makeDirectory($temp_dir, $mode = 0755, true, true); // Формируем уникальную директорию

        $zip_path = $temp_dir . '/roadlist.zip'; // Путь где будет храниться скачиваемый .zip архив

        if ($request->second_customer) { // Проверка на второго заказчика
            $template = storage_path('app/template/out_template_2.xlsx'); // получаем общий шаблон с двумя заказчиками
            $customer_offset = 2; // Выставляем смещение для корешка с двумя заказчиками
        } else {
            $template = storage_path('app/template/out_template.xlsx'); // получаем общий шаблон c одним заказчиком
        }


        $zip = new ZipArchive();
        $zip->open($zip_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template); // Общий объект шаблона (корешка)
        $activeSheet1 = $spreadsheet->getSheetByName('стр1'); // Получаем вкладку
        $activeSheet2 = $spreadsheet->getSheetByName('стр2'); // Получаем вкладку

        $tax_saving = []; // Хранение километража для машины

        $price = 0;

        foreach ($register as $key => $item) {
            $taxation = $taxation_collect->get( $item->tax_table ); // Получаем нужную правую таблицу изходя из заданного значения
            if($request->manual_price){
                $price = $item->price;
            }else{
                $price = (float)$request->price;
            }


            // выставляем значения на первом листе
            $activeSheet1->setCellValue('DQ53', $price); // Выставляем цену
            $activeSheet1->setCellValue('DP55', $request->tax_user); // Выставляем таксировщика
            $activeSheet1->setCellValue('FG55', $request->exp_user); // Выставляем начальника эксплуатации
            $activeSheet1->setCellValue('DP46', $item->n1 . ' ' . $item->n2); // Выставляем номер путевого листа
            $activeSheet1->setCellValue('DQ52', $item->success); // Выставляем количество выполенных поездок
            $activeSheet1->setCellValue('ED52', $taxation[(int)$item->road_count]->total); // Выставляем всего
            $activeSheet1->setCellValue('EK52', $taxation[(int)$item->road_count]->gruz); // Выставляем с гузом
            $activeSheet1->setCellValue('FE52', $item->volume); // Выставляем объём
            // формируем дату
            $date = Carbon::create($item->date);
            $activeSheet1->setCellValue('EH46', $date->format('d')); // Выставляем день
            $activeSheet1->setCellValue('EM46', $month_rus[$date->month - 1]); // Выставляем месяц
            $activeSheet1->setCellValue('FH46', $date->format('yy')); // Выставляем год

            if ($request->price_type == 'cr') {
                $activeSheet1->setCellValue('FW54', $item->success * $price); // Формируем "Всего к оплате" через количество
            }

            if ($request->price_type == 'cube') {
                $activeSheet1->setCellValue('FW54', $item->volume * $price); // Формируем "Всего к оплате" через кубы
            }


            // выставляем значения на втором листе
            $activeSheet2->setCellValue('P36', $request->organization); // Выставляем организацию
            $activeSheet2->setCellValue('T' . (38), $item->car_mark); // Выставляем марку автомобиля
            $activeSheet2->setCellValue('V40', $request->first_customer); // Выставляем первичного заказчика
            if ($request->second_customer) { // Выставляем второго заказчика если он есть
                $activeSheet2->setCellValue('V42', $request->second_customer); // Выставляем первичного заказчика
            }

            // Начало расчёта километража
            if (!array_key_exists($item->number, $tax_saving)) {
                $tax_saving[$item->number] = $item->first_tax; // Выставляем самый первый километраж для машины
            } else {
                $tax_saving[$item->number] += (int)mt_rand(75, 150); // Добавляем рандомное значение для начального километража ( Машина может ездить на тех обслуживание )
            }
            $activeSheet2->setCellValue('BP' . (42 + $customer_offset), $tax_saving[$item->number]); // Выставляем начальный километраж
            $tax_saving[$item->number] += $taxation[(int)$item->road_count]->total;
            $activeSheet2->setCellValue('BP' . (44 + $customer_offset), $tax_saving[$item->number]); // Выставляем конечный километраж
            // Конец расчёта километрожа

            $activeSheet2->setCellValue('BU' . (38 + $customer_offset), $item->number); // Выставляем государственный номерной знак
            $activeSheet2->setCellValue('W' . (42 + $customer_offset), $item->date . ','); // Выставляем дату
            $activeSheet2->setCellValue('W' . (44 + $customer_offset), $item->date . ','); // Выставляем дату
            $activeSheet2->setCellValue('AH' . (42 + $customer_offset), $taxation[(int)$item->road_count]->prb); // Выставляем время отбытия
            $activeSheet2->setCellValue('AH' . (44 + $customer_offset), $taxation[(int)$item->road_count]->ub); // Выставляем время убытия
            $activeSheet2->setCellValue('Y' . (48 + $customer_offset), $item->road_count); // Выставляем количество поездок


            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet); // Создаём новый .xlsx документ но основе копии
            $file_name = $item->n1 . ' ' . $item->n2; // Формируем имя документа
            $writer->save($temp_dir . "/{$file_name}.xlsx"); // Сохраняем документ в определённую директорию
            $zip->addFile($temp_dir . "/{$file_name}.xlsx", "{$file_name}.xlsx"); // Добавляем этот же файл в директорию
        }

        // Заполним реестр данными
        $activeSheet_register->setCellValue('B6', $request->first_customer); // Выставляем первого заказчика
        $activeSheet_register->setCellValue('B7', $request->organization); // Выставляем организацию


        // Сохранение регистра
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet_register); // Создаём новый .xlsx документ но основе копии
        $file_name = 'Register'; // Формируем имя документа
        $writer->save($temp_dir . "/{$file_name}.xlsx"); // Сохраняем документ в определённую директорию
        $zip->addFile($temp_dir . "/{$file_name}.xlsx", "{$file_name}.xlsx"); // Добавляем этот же файл в директорию


        $zip->close(); // Закрываем работу с архивом

        // Указываем заголовок на скачку файла
        $headers = array(
            'Content-Type' => 'application/octet-stream',
        );

        return response()->download($zip_path, 'Roadlist.zip', $headers); // Отправляем архив клиенту
    }


    public function reg(Request $request)
    {


    }
}
