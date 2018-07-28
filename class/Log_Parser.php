<?php
/**
 * Created by PhpStorm.
 * User: Radioactiveman
 * Date: 27.07.2018
 * Time: 17:40
 */

namespace Parser;


class Log_Parser
{
    private $path_to_file;
    private $lines = 0; //Количество строк в логе
    private $views = 0; //Количество просмотров
    private $urls = 0;
    private $urls_arr = array();
    private $traffic = 0;
    private $crawlers = array(
        'Google' => 0,
        'Bing' => 0,
        'Baidu' => 0,
        'Yandex' => 0,
        'Rambler' =>0,
    );
    private $status_codes = array();
    //private $all_arr = array();

    function __construct(string $log_path)
    {
        $this->path_to_file = $log_path;
    }

    public function read_log_file()
    {
        $file = fopen($this->path_to_file,'r') or die ('Не удаётся открыть указанный файл');
        while (!feof($file)) {
            $log_line = trim(fgets($file));//Вывод содержимого файла построчно
            $this->parse_string_of_log_file($log_line);
        }
        fclose($file);
    }

    public function print_results():array
    {
        $results = array();
        $results['lines'] = $this->lines;
        $results['views'] = $this->views;
        $results['unique_urls'] = $this->urls;
        $results['urls_list'] = $this->urls_arr;
        $results['traffic'] = $this->traffic;
        $results['crawlers'] = $this->crawlers;
        $results['status_codes'] = $this->status_codes;
        //$results['all'] = $this->all_arr;
        return $results;
    }

    private function parse_string_of_log_file(string $log_str)
    {
        //$pattern = "/(\S+)(\s+-|\S) (\S+|-) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\d+|\d+ - \d+) (\d+) \"(.*?\) (.*?\/.*?\)) (.*?))\"/";
        $pattern = "/(\S+)(\s+-|\S) (\S+|-) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\d+|\d+ - \d+) (\d+) \"(.*?)\" \"(.*?((\) (.*?)\/)|((.*?;){4}) (.*?)\/).*?)\"/";
        preg_match($pattern, $log_str, $log_str_arr);
        //$this->all_arr[] = $log_str_arr;
        $this->lines++;

        $ip = $log_str_arr[1]; //ip-адрес запроса
        $method = $log_str_arr[7]; //метод
        $path_to_script = $log_str_arr[8]; //выполняемый скрипт
        $status = $log_str_arr[10]; //статус ответа
        $transfer = $log_str_arr[11]; //передано данных
        $url = $log_str_arr[12]; //адрес сайта
        $crawler = $log_str_arr[16];
        if ($crawler == '')
            $crawler = $log_str_arr[19];//Поисковик для 301 статуса


        if (!in_array($url,$this->urls_arr)) {//Если в массиве уникальных адресов нет текущего адреса
            $this->urls++;
            $this->urls_arr[] = $url;
        }

        $this->traffic += $transfer;

        $status_arr = explode(' ',trim($status));
        $status = $status_arr[0];
        if ($status != 301)
            $this->views++; //Просмотр какой-либо страницы считается при любом статусе кроме 301?
        if (array_key_exists($status, $this->status_codes)) {
            $this->status_codes[$status]++;
        } else {
            $this->status_codes[$status] = 1;//Найден первый указанный статус
        }

        if (!in_array($url,$this->urls_arr)) {//Если в массиве уникальных адресов нет текущего адреса
            $this->urls++;
            $this->urls_arr[] = $url;
        }

        foreach ($this->crawlers as $key => $value) {
            if(preg_match('/^' . $key . '(.*?)/i',$crawler)) //Если в строке есть упоминание по одному из ключей массива поисковиков, то значение увеличикается
                $this->crawlers[$key]++;
        }
    }
}