<?php

namespace Forms\Controller;

class Admin extends \Cockpit\AuthController {

    public function index() {

        return $this->render('forms:views/index.php');
    }

    public function form($name = null) {

        $form = [ 'name'=>'', 'in_menu' => false ];

        if ($name) {

            $form = $this->module('forms')->form($name);

            if (!$form) {
                return false;
            }
        }

        return $this->render('forms:views/form.php', compact('form'));
    }

    public function entries($form) {

        $form = $this->module('forms')->form($form);

        if (!$form) {
            return false;
        }

        $count = $this->module('forms')->count($form['name']);

        $form = array_merge([
            'sortable' => false,
            'color' => '',
            'icon' => '',
            'description' => ''
        ], $form);

        $view = 'forms:views/entries.php';

        if ($override = $this->app->path('#config:forms/'.$form['name'].'/views/entries.php')) {
            $view = $override;
        }

        return $this->render($view, compact('form', 'count'));
    }

    private function convert_to_csv($input_array, $output_file_name, $delimiter)
    {
        /** open raw memory as file, no need for temp files, be careful not to run out of memory thought */
        $f = fopen('php://memory', 'w');
        /** loop through array  */
        foreach ($input_array as $line) {
            /** default php csv handler **/
            fputcsv($f, $line, $delimiter);
        }
        /** rewrind the "file" with the csv lines **/
        fseek($f, 0);
        /** modify header to be downloadable csv file **/
        header('Content-Encoding: UTF-8');
        header("Content-type: application/csv; charset=UTF-8");
        header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
        header("Pragma: no-cache");
        header("Expires: 0");
        echo "\xEF\xBB\xBF";

        /** Send file to browser for download */
        fpassthru($f);
    }

    public function export($form) {

        if (!$this->app->module("cockpit")->hasaccess("forms", 'manage')) {
            return false;
        }

        $form = $this->module('forms')->form($form);

        if (!$form) return false;

        $entries = $this->module('forms')->find($form['name']);

        // return json_encode($entries, JSON_PRETTY_PRINT);
        $exportData = [];
        $headCols = [];
        //make data
        foreach($entries as $key=>$item){
            $rowData=[];
            //get data base on header column
            foreach($item as $k=>$v){
                if ($key == 0) {
                    if ($k == "data"){
                        foreach($v as $k_data=>$v_data){
                            $headCols[] = $k_data;
                        }
                    }
                    else{
                        $headCols[] = $k;
                    }
                }
                //format day column
                if(in_array($k, ['_modified','_created']))$v=date('j F, Y',$v);
                if ($k == "data"){
                    foreach($v as $k_data=>$v_data){
                        if ($k_data == "phone" || $k_data == "time")
                            $rowData[]='="' . $v_data . '"';
                        else  
                            $rowData[]=$v_data;                      
                    }              
                }
                else
                    $rowData[]=$v;
            }   
            if ($key == 0) {
                $exportData[] = $headCols;
            }             
            $exportData[]=$rowData;
        }

        $this->convert_to_csv($exportData, $form['name'].'.csv', ',');
        // return json_encode($entries, JSON_PRETTY_PRINT); 
        return '';
    }
}
