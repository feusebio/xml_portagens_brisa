<?php

$filepath = '/var/www/ReadXML/Extracto_mini.xml';
$content = file_get_contents($filepath);

$obj = new ProcessaPortagens($content);
$obj->processa_nodes();

class ProcessaPortagens {

    private $last_ini_periodo,
            $last_fim_periodo,
            $first_date,
            $ini_data,
            $fim_data,
            $date_searched;
    
    static private $contentXML;

    function __construct($content) {
        self::__init($content);
    }
    
    static private function __init($content) {
        self::$contentXML = $content;
    }
    
    public function processa_nodes() {
        //print_r($content_xml);
        $xml = new SimpleXMLElement(self::$contentXML);

        $json = json_encode($xml);
        $xml_array = json_decode($json, TRUE);
        
        $id_extracto = $xml_array['@attributes']['id'];

        $documentoPortagem = new stdClass();
        $registosPortagem = new stdClass();

        /*
         * Obter cabeçalho do documento
         */

//        echo "Periodo: " . $xml_array['MES_EMISSAO'] . "\n";
//        echo "Extracto: " . $id_extracto . "\n";
//        echo "NIF: " . $xml_array['CLIENTE']['NIF'] . "\n";
//        echo "Nome: " . $xml_array['CLIENTE']['NOME']. "\n";
//        echo "Morada: " . $xml_array['CLIENTE']['MORADA'] . "\n";
//        echo "Localidade: " . $xml_array['CLIENTE']['LOCALIDADE'] . "\n";
//        echo "CP: " . $xml_array['CLIENTE']['CODIGO_POSTAL'] . "\n";

        $documentoPortagem->id_extracto = $id_extracto;
        $documentoPortagem->nif = $xml_array['CLIENTE']['NIF'];
        $documentoPortagem->data_ini = $xml_array['MES_EMISSAO']; //Avaliar as datas das transações
        $documentoPortagem->data_fim = $xml_array['MES_EMISSAO'];

        /*
         * Obter transações
         */
        
        $identificadores_A = $xml_array['IDENTIFICADOR'];        
        unset($xml_array);
        
        foreach ($identificadores_A as $identificador) {
            $id = $identificador['@attributes']['id'];
            echo $identificador['MATRICULA'] . "\n" . $id;
            //foreach ($xml->xpath('//TRANSACCAO') as $trans) {
            $transaccao = $identificador['TRANSACCAO'];
            foreach ($transaccao as $t) {
                //echo $t['DATA_SAIDA'] . "Valor: " . $t['IMPORTANCIA'] . "\n";
                //$this->checkdata($t['DATA_SAIDA']);
      
                $out = $this->get_data_ini_fim_periodo($date_searched=$t['DATA_SAIDA']);
                
//                if ($first_date == 1) {
//                    $first_date = 2;
//                    $last_ini_periodo = strtotime($t['DATA_SAIDA']);
//                    $ini_data = $t['DATA_SAIDA'];
//                    $last_fim_periodo = strtotime($t['DATA_SAIDA']);
//                    $fim_data = $t['DATA_SAIDA'];
//                }
//
//                if (strtotime($t['DATA_SAIDA']) < $last_ini_periodo && strtotime($t['DATA_SAIDA']) < $last_fim_periodo) {
//                    $ini_data = $t['DATA_SAIDA'];
//                    $last_ini_periodo = strtotime($t['DATA_SAIDA']);
//                }
//                if (strtotime($t['DATA_SAIDA']) > $last_fim_periodo) {
//                    $fim_data = $t['DATA_SAIDA'];
//                    $last_fim_periodo = strtotime($t['DATA_SAIDA']);
//                }
            }
        }
        
        print_r("\n");
        print_r('-------------------------- PERIODO ---------------------------');
        print_r('DATA INICIAL: '.$this->ini_data);
        print_r("\n");
        print_r('DATA FINAL: '.$this->fim_data);
        print_r("\n");
        echo "INI: " . $out;
    }
    
    /* @var $data_str_ string data */
    private function checkdata($data_str){
       $data =  date("Y-m-d", strtotime($data_str));
       
       if (is_null($this->ini_data)) $this->ini_data = $data;
       $this->ini_data = $data < $this->ini_data ? $data : $this->ini_data;
       
       if (is_null($this->fim_data)) $this->fim_data = $data;
       $this->fim_data = $data > $this->fim_data ? $data : $this->fim_data;
    }
    
    
    
    /* @var $date_searched string data */
    final public function get_data_ini_fim_periodo($date_searched){
                $date_searched_TimeStamp = strtotime($date_searched);
        
                if (!$this->first_date) {
                    $this->first_date = 1;
                    $this->last_ini_periodo = $date_searched_TimeStamp;
                    $this->ini_data = $date_searched;
                    $this->last_fim_periodo = $date_searched_TimeStamp;
                    $this->fim_data = $date_searched;
                }

                if ($date_searched_TimeStamp < $this->last_ini_periodo && $date_searched_TimeStamp < $this->last_fim_periodo) {
                    $this->ini_data = $date_searched;
                    $this->last_ini_periodo = $date_searched_TimeStamp;
                }
                if ($date_searched_TimeStamp > $this->last_fim_periodo) {
                    $this->fim_data = $date_searched;
                    $this->last_fim_periodo = $date_searched_TimeStamp;
                }
                return "INI: " . $this->ini_data . "FIM: " . $this->fim_data;
    }

}