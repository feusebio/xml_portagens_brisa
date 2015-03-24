<?php

$filepath = '/var/www/ReadXML/Extracto_mini.xml';
//$filepath = '/var/www/ReadXML/Extracto_001774523_03_2013_bk.xml';
//$filepath = '/var/www/ReadXML/Extracto_001027263_02_2013.xml';
$content = file_get_contents($filepath);

$obj = new ProcessaPortagens($content);
$obj->processa_nodes();

class ProcessaPortagens {
    CONST VIAVERDE = 6;
    CONST TIPOCONSUMO = 2;
    CONST IDPRODUTOPORTAGEM = 1;
    CONST IDEMPRESA = 300;
    
    private $last_ini_periodo,
            $last_fim_periodo,
            $first_date,
            $ini_data,
            $fim_data,
            $date_searched;
            
    
    static private
            $contentXML,
            $id_extracto,
            $nif,
            $valor_factura;

    function __construct($content) {
        self::__init($content);
    }
    
    static private function __init($content) {
        self::$contentXML = $content;
    }
    
    
    private function setTransaccoes($_t,$id,$matricula){
        $dataEntrada = $_t['DATA_ENTRADA']=='null' ? null : $_t['DATA_ENTRADA'] . " " . $_t['HORA_ENTRADA'];
        $entrada = count($_t['ENTRADA']) == 0 ? null : $_t['ENTRADA'];
        $row = array(
            'IdExtracto'            => self::$id_extracto,
            'NumIdentificador'      => $id,
            'Matricula'             => $matricula,
            'DataEntrada'           => $dataEntrada,
            'Entrada'               => $entrada,
            'DataSaida'             => $_t['DATA_SAIDA'] . " " . $_t['HORA_SAIDA'],
            'Saida'                 => $_t['SAIDA'],
            'Importancia'           => $_t['IMPORTANCIA'],
            'ValorDesconto'         => $_t['VALOR_DESCONTO'],
            'TaxaIva'               => $_t['TAXA_IVA'],
            'ValorIva'              => $_t['VALOR_IVA'],
            'Operador'              => $_t['OPERADOR'],
            'IdFornecedor'          => self::VIAVERDE,
            'IdTipoConsumo'         => self::TIPOCONSUMO
            );
            
            if(is_null($dataEntrada)) {
                unset($_t['Entrada']);
                unset($_t['DataEntrada']);
            }
        $this->checkdata($_t['DATA_SAIDA']);
        return $row;
    }
    
    public function processa_nodes() {
        //print_r($content_xml);
        $xml = new SimpleXMLElement(self::$contentXML);

        $json = json_encode($xml);
        $xml_array = json_decode($json, TRUE);
        //print_r($xml_array);
        /*
         * Obter cabeçalho do documento
         */
        self::$id_extracto = $xml_array['@attributes']['id'];
        self::$nif = $xml_array['CLIENTE']['NIF'];
        self::$valor_factura = $xml_array['TOTAL'];

        /*
         * Obter transações
         */
        
        $identificadores_A = $xml_array['IDENTIFICADOR'];
        //print_r($identificadores_A);
        
        unset($xml_array);
        foreach ($identificadores_A as $identificador) {
            
            $id = $identificador['@attributes']['id'];
            $matricula = $identificador['MATRICULA'];
            $transaccao = $identificador['TRANSACCAO'];
            
            if (isset($transaccao['DATA_SAIDA'])) $resultTransacao[] = $this->setTransaccoes($transaccao,$id,$matricula);
            else foreach ($transaccao as $t) $resultTransacao[] = $this->setTransaccoes($t,$id,$matricula);
        }
        
        $resultDocumento = array(
            'IdEmpresa' => self::IDEMPRESA,
            'IdTipoDocumento' => self::IDPRODUTOPORTAGEM,
            'IdExtracto' => self::$id_extracto,
            'Nif' => self::$nif,
            'Valor' => self::$valor_factura,
            'DataIni' => $this->ini_data,
            'DataFim' => $this->fim_data,
            'Transaccoes' => $resultTransacao
        );
        
        //print_r(count($resultTransacao));
        //print_r($resultDocumento);
        return $resultDocumento;
    }
    
    /* @var $data_str_ string data */
    private function checkdata($data_str){
       $data =  date("Y-m-d", strtotime($data_str));
       
       if (is_null($this->ini_data)) $this->ini_data = $data;
       else $this->ini_data = $data < $this->ini_data ? $data : $this->ini_data;
       
       if (is_null($this->fim_data)) $this->fim_data = $data;
       else $this->fim_data = $data > $this->fim_data ? $data : $this->fim_data;
    }
}
?>