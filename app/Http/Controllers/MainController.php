<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $temp_dir = public_path('temp/' . Str::uuid());
        File::makeDirectory($temp_dir, $mode = 0755, true, true); // Формируем уникальную директорию

        $zip_path = $temp_dir . '/roadlist.zip'; // Путь где будет храниться скачиваемый .zip архив
        $template = storage_path('app/template/out_template.xlsx'); // получаем общий шаблон

        $zip = new ZipArchive();
        $zip->open($zip_path, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($template); // Общий объект
        $activeSheet = $spreadsheet->getActiveSheet(); // Получаем вкладку
        $activeSheet->setCellValue('P36', 'some data');


        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet); // Создаём новый .xlsx документ но основе копии
        $writer->save($temp_dir . '/list_1.xlsx'); // Сохраняем документ в определённую директорию
        $zip->addFile($temp_dir . '/list_1.xlsx', 'list_1.xlsx'); // Добавляем этот же файл в директорию



        $zip->close(); // Закрываем работу с архивом

        // Указываем заголовок на скачку файла
        $headers = array(
            'Content-Type' => 'application/octet-stream',
        );

        return response()->download($zip_path, 'Roadlist.zip', $headers); // Отправляем архив клиенту
    }
}
