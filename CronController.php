<?php

namespace App\Http\Controllers;

use App\Models\DAO\ElementDAO;
use App\Models\DAO\PerformancesDAO;
use App\Models\dao\TrafficDAO;
use App\Models\dao\AvailabilityDAO;
use App\Models\dao\AdditionalParametersDAO;
use App\Models\entity\reports\AdditionalParameter;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\dao\AlarmDAO;
use App\Models\entity\alarms\Alarm;
use DB;
use Carbon\Carbon;
use App\Models\entity\security\User;
use App\Models\entity\reports\Traffic;
use App\Models\entity\reports\Performance;

class CronController extends Controller
{

  const ENTITY_PATH = 'App\Models\entity\reports\\';


  public function newxml()
  {

  
    $dates=fecha_start_end(true);
    $dates['date_start']='1563372000000';
    $dates['date_end']='1563375599000'; 
    $data = get_data_service('getStringXmlAdditionalParameters', $dates);
    /*dd($data);*/
    echo "<pre>";
    print_r($data);
    dd();


//    $archivo = base_path().'/' .'xml/parameters_peak.txt';
//
//    //$abrir = fopen($archivo,'r+');
//
//    $abrir2 = fopen($archivo,'r+');
//    //dd(filesize($archivo));
//    $contenido = fread($abrir2,filesize($archivo));
//   // $contenido = implode("\n", $contenido);
//    dd($contenido);
//    $archivo = base_path().'/' .'xml/parameters_peak.txt';
//
//    $abrir = fopen($archivo,'r+');
//
//    $k=0;
//    while(!feof($abrir))
//    {
//      //echo fgets($abrir). "<br />";
//      print_r(fgets($abrir));
//
//      echo $k;
//      echo '<br>';
//      // echo '<hr>';
////      echo '<hr>';
////      echo fgets($abrir);
////      echo '<hr>';
////       $array = explode('|', fgets($abrir));
////       if ($array[0] == $hour && $array[1] == $element) {
////
////         $compare = $array[2];
////         $n = true;
////         $key = $k;
////         break;
////       }
////
//      $k++;
//    }
//dd();
//    $dato= [
//      '0'=>['hour'=>13,'element'=>'AYA-CHUNGUI-DI-SASXP-01','value'=>'-5.275361'],
//      '1'=>['hour'=>13,'element'=>'AYA-CARMENAL-DI-SASXP-01','value'=>'-40.0'],
//      '2'=>['hour'=>13,'element'=>'AYA-CARMENAL-DI-SASXP-01','value'=>'-5.275361'],
//          ];
//    $archivo = base_path().'/' .'xml/parameters_peak.txt';
//    foreach ( $dato as $k => $v ){
//
//            $val = writeFile($v['hour'],$v['element'],$v['value']);
//
//            if(!$val){
//              writeFileSimple($archivo,$v['hour'].'|'.$v['element'].'|'.$v['value']);
//            }
//
//    }
//    dd();
//
//dd();
//use of service
    //$dates = fecha_start_end();

    // $data = get_data_service('getStringXmlUseOfService', $dates);
    ///echo '<pre>';
    //print_r($data);
    // dd();

//calidad
//    $dates = fecha_start_end(true);
//
//    $data = get_data_service('getStringXmlAdditionalParameters', $dates);
//
//    echo '<pre>';
//    print_r($dates);
//    print_r($data);
//
//    dd($data,'xd');

    /*$this->useOfServiceWs();*/
//    dd();
    /*$this->performancesWs();*/
    $this->useOfServiceWstest();
    dd();
    $dates = fecha_start_end(true);
    
    // se resta 3300000 para completar los 55 min restantes .. para obtener data de una hora atras.
    /*$dates['date_start']=$dates['date_start']-3300000;*/
     $dates['date_start']='1538039760000';
    $dates['date_end']='1538039819000';   
    /*dd($dates);*/
    $data = get_data_service('getStringXmlUseOfService', $dates);
    
echo "<pre>";
print_r($data);
    dd();
    
    /*Mostrar Data de la WebService asignando fecha 
    $dates = fecha_start_end();
    $dates['date_start']='1536302880000';
    $dates['date_end']='1536302939000';     
    $data = get_data_service('getStringXmlUseOfService', $dates);
	echo "<pre>";
    print_r($data);
    dd();*/
    /*$array_template = config('webservice.use_of_service');

    $this->test_use_service($data['result']['equipment.InterfaceAdditionalStatsLogRecord'], $array_template);
    dd();*/
  }

  public function cleanFile(){

    cleanFile();
  }

  //////USE OF SERVICE -START/////

  /**
   * get data use of service from werservice
   */
  public function useOfServiceWs()
  {

     
    saveFileLog('xml/uso_de_servicio.txt', '');

    $dates = fecha_start_end();


    $data = get_data_service('getStringXmlUseOfService', $dates);
    //print_r($data);exit;
    $save = $this->processDataUseOfService($data);

    $file = ['xml/uso_de_servicio.txt' => true];
  
    $data=$this->storeReportWs($save, 'Traffic', $file);

    insertdatatxtuseofservice($data,$dates['date_start']);


       
        
  }

  /**
   * process data use of service
   * @param $data data of use of service
   */
  public function processDataUseOfService($data)
  {

    $array_template = config('webservice.use_of_service');

    return $this->set_array_use_service($data['result']['equipment.InterfaceAdditionalStatsLogRecord'], $array_template);

  }

  public function set_array_use_service($array_data, $array_template)
  {

    $array = [];
    $i = 0;
    $periodictTime = 0;

    foreach ($array_data as $key => $value) {

      $displayname = false;

      ksort($value);

      foreach ($value as $k => $v) {

        if($k =='displayedName'){
          $puerto_display=$v;
          $port = substr($v, 0, 4);

          if( $port =="Port" ){

            $displayname = true;

          }
        }

        if($displayname){
          switch ($k) {

            case 'monitoredObjectSiteName' :

              $array_template['element_identifier'] = $v;

              break;

            case 'periodicTime' :

              $periodictTime = $v;
              $array_template['periodic_time'] = $v;

              break;
            //el periodictTime trae valor cero
            //case 'receivedTotalOctetsPeriodic' :
            /*case 'receivedTotalOctets' :

//            if ($periodictTime) {
//
//              $array_template['bw_received'] = ($v / $periodictTime);// / 1000 para ser Kilobyte se hace en la query
//            }
              $array_template['bw_received'] = $v/1024;

              break;
            */
            case 'receivedTotalOctetsPeriodic' :

//            if ($periodictTime) {
//
//              $array_template['bw_received'] = ($v / $periodictTime);// / 1000 para ser Kilobyte se hace en la query
//            }
              $array_template['bw_received'] = ($v/$periodictTime);
              $array_template['received_total_octets_periodic'] = $v;

              break;

            case 'timeCaptured' :

              $fecha_minute = getDateMinStartMinEnd($v, $periodictTime);
              $array_template['traffic_date'] = $fecha_minute['date_start_format'];
              $array_template['minute_start'] = $fecha_minute['minute_start'];
              $array_template['minute_end'] = $fecha_minute['minute_end'];

              $array_template['element_interface']=$puerto_display."#".$v;

              break;
            //el periodictTime trae valor cero
            //case 'transmittedTotalOctetsPeriodic' :
            /*case 'transmittedTotalOctets' :

//            if ($periodictTime) {
//
//              $array_template['bw_transmition'] = ($v / $periodictTime);// /1000 para ser Kilobyte se hace en la query
//            }
              $array_template['bw_transmition'] = $v/1024 ;

              break;*/
            case 'transmittedTotalOctetsPeriodic' :

//            if ($periodictTime) {
//
//              $array_template['bw_transmition'] = ($v / $periodictTime);// /1000 para ser Kilobyte se hace en la query
//            }
              $array_template['bw_transmition'] = ($v/$periodictTime);
              $array_template['transmitted_total_octets_periodic'] = $v ;

              break;
            default :

              break;
          }
        }


      }
      if($array_template['bw_transmition']!=0 and $array_template['bw_received']!=0 and $array_template['transmitted_total_octets_periodic']!=0 and $array_template['received_total_octets_periodic']!=0)
      {
        $array[$i] = $array_template;
      }

      
      $i++;
    }

    $string_in = collect($array)->implode('element_identifier', ',');

    $string_in = explode(',', $string_in);
    //trae solo los elementos que tenemos con la tabla elements
    $nodo_parent_name = ElementDAO::getNodoParent($string_in);

    foreach ($array as $key => $value) {

      $encontro = false;

      foreach ($nodo_parent_name as $k => $v) {

        if ($value['element_identifier'] == $v->identifier) {

          $array[$key]['nodo_identifier'] = $v->parent_id;

          $encontro = true;
        }

      }
      if (!$encontro) {

        unset($array[$key]);
      }

    }
    //echo '<pre>';
    //print_r($array);
    //dd('antes de guardar ');

    return $array;

  }

  //////PERFORMANCES -START/////

  /**
   * Get data performances from webservice
   */
  public function performancesWs()
  {

    saveFileLog('xml/calidad.txt', '');

    sleep(2);

    $dates = fecha_start_end(true);
  
    /*sleep_ws('uso_de_servicio.txt');*/

    $data = get_data_service('getStringXmlPerformances', $dates);
    /*echo "<pre>";
    print_r($data);exit;*/
    $save = $this->processDataPerformances($data['result']['svt.TunnelPingResult']);
    
    //$file = ['xml/uso_de_servicio.txt' => '', 'xml/calidad.txt' => true];
    $file = ['xml/uso_de_servicio.txt' => ''];

    $data=$this->storeReportWs($save, 'Performance', $file);
   

    /*lee archivo consult_use_of_service.txt , por cada registro para calcular upload_speed,download_speed y percentage_congestion*/

     foreach ($data as $key => $column) {
      
        $bandwidth_c=NULL;
        $upload_speed=NULL;
        $download_speed=NULL;
        $percentage_congestion=NULL;


        $fecha_hora=explode(":", $column['event_date']);
        $set_fecha_hora=$fecha_hora[0].":".$fecha_hora[1];

        $array_element[0]=$column['element_identifier'];

                          
        $archivo = base_path().'/' .'xml/consult_use_of_service.txt';

            
            $abrir = fopen($archivo,'r+');

            $k=0;

             while(!feof($abrir))
             {
              $linea=fgets($abrir);
/*fecha_hora_min|elemento|transmitted_total_octets_periodic|received_total_octets_periodic|periodic_time*/
                   $array = explode('|', $linea);
         
                 if ($array[0] == $set_fecha_hora && $array[1] == $array_element[0]) {

                   
              $bandwidth_c = ($array[2]*8)/$array[4];

              $upload_speed = $bandwidth_c;

              $download_speed = ($array[3]*8)/$array[4];

              /*$capacity_port = $bandwidth_consumption['data'][0]->capacity_port;*/
              
              $capacity_port_query = ElementDAO::getNodoParent($array_element);
              $capacity_port=$capacity_port_query[0]->capacity_port;


                   $k++;
                   break;
                 }




             }
             fclose($abrir);

              if($k>0)
              {

              if ($bandwidth_c != null) {
              /*$percentage_congestion = number_format($bandwidth_c / $capacity_port, 13);*/
              $percentage_congestion = number_format(((($bandwidth_c*1000) / $capacity_port)*100),13);
            } else {

              $percentage_congestion = null;
            }

              $path_entity = CronController::ENTITY_PATH . 'Performance';
               $store = $path_entity::find($column['id']);
               $store->percentage_congestion=$percentage_congestion;
               $store->upload_speed=$upload_speed;
               $store->download_speed=$download_speed;
               $store->save();

               }           
               
    }




        
  }

  /**
   * process data of performaces
   * @param $data data of performances
   */
  public function processDataPerformances($data)
  {

    $array_template = config('webservice.performaces');

    return $this->set_array_performaces($data, $array_template);
  }

  /**
   * @param $data , data of ws
   * @param $array_template array to fill
   * @return array
   */
  public function set_array_performaces($data, $array_template)
  {

    $array = [];
    $i = 0;
    $capacity_port=1;


    foreach ($data as $key => $value) {

      ksort($value);

      $value["bandwidth_consumption"] = null;
      $value["percentage_congestion"] = null;
      $value["upload_speed"] = null;
      $value["download_speed"] = null;


      foreach ($value as $k => $v) {

        switch ($k) {
          //fromNodoId
          case 'fromNodeId' :

            $array_template['fromNodeId'] = $v;

            break;

          case 'packetsLost' :

            $array_template['packet_loss'] = $v;

            break;

          case 'roundTripJitter' :

            /*$array_template['jitter'] = $v;*/
            $array_template['jitter'] = $v/1000;

            break;

          case 'timeCaptured' :


            $date = getEventDate($v);

            $array_template['event_date'] = $date['date_start_format'];

            break;

          //el ancho de banda debo buscar en la tabla uso de servicio.
            case 'averageRoundTripTime' :


            

            /*$array_template['latency'] = $v;*/
            $array_template['latency'] = $v/1000;

            break;

          
          case 'bandwidth_consumption' :

            //array $date_seconds_false created in timeCaptured
            /*$parameters_query = getDateMinStartMinEndBW($date);*/

            //consulta de ancho de banda el elemento,en la fecha y minuto inicio y fin .

            /*$element_ip = $array_template['fromNodeId'];*/

            /*$bandwidth_consumption = TrafficDAO::getBandWidth($element_ip, $parameters_query['date_bw'], $parameters_query['minute_start_bw'], $parameters_query['minute_end_bw']);*/
            /*echo "<pre>";
            print_r($bandwidth_consumption);
            dd();*/
            /*if ($bandwidth_consumption['count']) {*/

              $array_template['bandwidth_consumption'] = NULL;

              $array_template['upload_speed'] = $array_template['bandwidth_consumption'];

              $array_template['download_speed'] = NULL;

              $capacity_port = NULL;


            /*}*/

            break;

          case 'percentage_congestion' :

            /*if ($array_template['bandwidth_consumption'] != null) {


              $array_template['percentage_congestion'] = number_format($array_template['bandwidth_consumption'] / $capacity_port, 13);


            } else {*/

              $array_template['percentage_congestion'] = null;
            /*}*/

            break;

          default :

            break;
        }


      }


      $array[$i] = $array_template;
      $i++;
    }


    $string_in = collect($array)->implode('fromNodeId', ',');

    $string_in = explode(',', $string_in);

    $nodo_parent_name = ElementDAO::getNodoParentIp($string_in);

    foreach ($array as $key => $value) {

      $encontro = false;

      foreach ($nodo_parent_name as $k => $v) {

        if ($value['fromNodeId'] == $v->ip) {

          $array[$key]['element_identifier'] = $v->identifier;
          $encontro = true;
        }

      }
      if (!$encontro) {

        unset($array[$key]);
      }

    }
    // dd($array);
    return $array;


  }

  public function storeReportWs($data, $model, $file = false)
  {
    $path_entity = CronController::ENTITY_PATH . $model;

    DB::beginTransaction();

    try {

      foreach ($data as $key => $value) {

        $store = $path_entity::create($value);
        $data[$key]['id']=$store->id;
      }
    
      DB::commit();

      if ($file) {

        foreach ($file as $key => $value) {

          saveFileLog($key, $value);
        }

      }
      return $data;
    } catch (\Exception $e) {

      DB::rollBack();

    }

  }

    public function storeReportWstest($data, $model, $file = false)
  {
    $path_entity = CronController::ENTITY_PATH . $model;

    DB::beginTransaction();

    try {

      foreach ($data as $key => $value) {

        $store = $path_entity::create($value);
        $data[$key]['id']=$store->id;
      }
    
      DB::commit();

      if ($file) {

        foreach ($file as $key => $value) {

          saveFileLog($key, $value);
        }

      }
      return $data;
    } catch (\Exception $e) {

      DB::rollBack();

    }

  }

//////ADDITIONAL PARAMETERS START/////

  /**
   * get data additional parameters from webservice
   */
  public function additionalParametersWs()
  {

    $start = microtime(true);

    //sleep(2);

    $dates = fecha_start_end(true);
	  /*$dates['date_start']='1552060800000';
    $dates['date_end']='1552064399000'; */
    // sleep_ws('calidad.txt');

    $data = get_data_service('getStringXmlAdditionalParameters', $dates);
//    print_r($data);exit;
     /*----------- crea txt con todos los datos devueltos por nokia--------*/

    $fecha_actual = date("YmdHis");
    $nombre="/var/www/html/GILAT-SMRT/app/Http/Controllers/NOKIA/T20_NOKIA_XML_".$fecha_actual."_MethodGetDataServices.txt";
    $file_xml = fopen($nombre, "w");


    foreach ($data['result']['equipment.DDMStatsLogRecord'] as $key_xml => $value_xml) {
            fwrite($file_xml, "[".$key_xml."] => Array" . PHP_EOL);
            fwrite($file_xml, "    (" . PHP_EOL);
      foreach ($value_xml as $key2_xml => $value2_xml) {

        fwrite($file_xml, "       [".$key2_xml."] => ".$value2_xml . PHP_EOL);
      }
     fwrite($file_xml, "    )" . PHP_EOL); 
 
    }

fclose($file_xml);
/*dd("termino");*/

    /*--------------------------------------------------------------------------------*/
    

    $save = $this->processDataAdditionalParameters($data['result']['equipment.DDMStatsLogRecord']);
   
    $file = ['xml/calidad.txt' => ''];

    $data=$this->storeReportWs($save, 'AdditionalParameter', $file);

     $data2=$data;
 
      foreach ($data as $key => $column) {
      
      
      
        $transmission_rate=NULL;
          $power_loss=NULL;


        $fecha_hora=explode(":", $column['event_date']);
        $set_fecha_hora=$fecha_hora[0].":".$fecha_hora[1];

        $array_element[0]=$column['element_identifier'];

                          
          $archivo = base_path().'/' .'xml/consult_use_of_service.txt';

            
            $abrir = fopen($archivo,'r+');

            $k=0;

             while(!feof($abrir))
             {
              $linea=fgets($abrir);
/*fecha_hora_min|elemento|transmitted_total_octets_periodic|received_total_octets_periodic|periodic_time*/
                   $array = explode('|', $linea);
         
                 if ($array[0] == $set_fecha_hora && $array[1] == $array_element[0]) {

             

              $transmission_rate = ($array[3]*8)/$array[4];


                   $k++;
                   break;
                 }




             }
             fclose($abrir);

                   /*--------------------------------------------------*/
              $element=$column['element'];
              $element_part=explode(" ", $element);
              $count_power=0;
              if($element_part[2]!='X:X/X/XX')
              {
                $cruzada=$element_part[2]." (DIR) ".$element_part[0];
                $fecha_cruzada=$column['event_date'];

                foreach ($data2 as $key_data2 => $value_data2) {
                    if($value_data2['element']==$cruzada && $value_data2['event_date']==$fecha_cruzada)
                    {
                        $power_loss=($column['txOutputPower'])-($value_data2['rxOpticalPower']);
                        $count_power++;
                        break;
                    }else
                    {
                      $power_loss=NULL;
                    }
                }
              }

              /*-----------------------------------------------------*/

            /*  if($k>0)
              {
*/
              $path_entity = CronController::ENTITY_PATH . 'AdditionalParameter';
               $store = $path_entity::find($column['id']);
               $store->transmission_rate=$transmission_rate;
               $store->power_loss=$power_loss;
               $store->save();

               $k=0;
               $count_power=0;

               /*}  */         
               
    }

    

  }

  public function processDataAdditionalParameters($data)
  {
    $array_template = config('webservice.additional_parameters');

    return $this->set_array_additional_parameters($data, $array_template);
  }

  public function set_array_additional_parameters($data, $array_template)
  {


    //$collection = collect($data);

    $array = [];

    $i = 0;

    $hoy = date("d/m/Y H:i");

    $year  = Carbon::createFromFormat('d/m/Y H:i', $hoy)->format('Y');
    $month = Carbon::createFromFormat('d/m/Y H:i', $hoy)->format('m');
    $day   = Carbon::createFromFormat('d/m/Y H:i', $hoy)->format('d');
    $hour  = Carbon::createFromFormat('d/m/Y H:i', $hoy)->format('H');

    $string_in = collect($data)->implode('monitoredObjectSiteId', ',');

    $string_in = explode(',', $string_in);

    $nodo_parent_name = ElementDAO::getNodoParentIp($string_in);

    foreach ($data as $key => $value) {

      $encontro = false;

      foreach ($nodo_parent_name as $k => $v) {

        if ($value['monitoredObjectSiteId'] == $v->ip) {

          $encontro = true;
          $data[$key]['puerto_origen'] = $v->puerto_origen;
          $data[$key]['puerto_destino'] = $v->puerto_destino;
          $data[$key]['identifierdest'] = $v->identifierdest;
        }

      }
      if (!$encontro) {

        unset($data[$key]);
      }

    }


//    $maximo_hour = AdditionalParametersDAO::getValueMax($year,$month,$day,$hour);
//
//    if( $maximo_hour->peak_value_optical_power ){
//
//      writeFile($hour ,$maximo_hour->peak_value_optical_power);
//
//    }


    foreach ($data as $key => $value) {


      ksort($value);
      $value['packet_loss'] = null;
      $value['peak_value_optical_power'] = null;
      $value['transmission_rate'] = null;


      foreach ($value as $k => $v) {

        switch ($k) {

          case 'displayedName' :

            /*$array_template['interface'] = $v;*/
            $puerto=$v;

            break;
          case 'monitoredObjectSiteId' :

            $array_template['ip'] = $v;

            break;
          case 'monitoredObjectSiteName' :

           
            $array_template['element_identifier'] = $v;

            $nodo=ElementDAO::getNodoParent2($v);

            if(count($nodo)!=0)
            {
                 foreach ($nodo as $key_nod => $value_nod) {
                    $array_template['node'] = $value_nod->parent_id;
                    
                  }

            }else
            {
                $array_template['node'] = NULL;
            }



            if($puerto!='')
            {
              
              $port_format=explode("-", $puerto);
              $port_format2=explode(" ", $port_format[0]);
              $port_set=$port_format2[1];

              $adjacent = ElementDAO::getAdjacent($port_set,$v);
              
              if(count($adjacent)!=0)
              {
                /*dd("entro");*/
                  foreach ($adjacent as $key_ad => $value_ad) {
                    $destine_element = $value_ad->identifier;
                    $array_template['destine_interface'] = $value_ad->destination_port;
                    
                  }

                  $array_template['element'] = $v.":".$port_set." (DIR) ".$destine_element.":".$array_template['destine_interface'];
              }else
              {
                $destine_element = NULL;
                $array_template['destine_interface'] = NULL;
                $array_template['element'] = $v.":".$port_set." (DIR) X:X/X/XX";
              }
              

            }


            break;
          case 'rxOpticalPower' :

            $array_template['rxOpticalPower'] = $v;

            break;

          case 'txOutputPower' :

            $array_template['txOutputPower'] = $v;

            break;

          case 'timeCaptured' :

            $date = getEventDate($v);//$date['date_start_format']
            $array_template['event_date']   = $date['date_start_format'];
            $array_template['timeCaptured'] = $v;

            break;

          case 'transmission_rate' :

            /*$parameters_query = getDateMinStartMinEndBW($date);

            $element_ip = $array_template['ip'];*/

            /*$bandwidth_consumption = TrafficDAO::getBandWidthtest($element_ip, $parameters_query['date_bw'], $parameters_query['minute_start_bw'], $parameters_query['minute_end_bw']);*/

            /*if ($bandwidth_consumption['count']) {*/
              //se trae el primero
              /*$array_template['transmission_rate'] = ($bandwidth_consumption['data'][0]->transmitted_total_octets_periodic*8)/$bandwidth_consumption['data'][0]->periodic_time;*/
              $array_template['transmission_rate'] = null;
            /*}*/


            break;

          case 'packet_loss' :

            /*$packet_loss = PerformancesDAO::getPacketLost($array_template['element_identifier'], $date['date_start_lost'], $date['date_end_lost']);*/

            /*if ($packet_loss['count']) {*/

              $array_template['packet_loss'] = 0;

            /*}*/

            break;

          case 'peak_value_optical_power':



//              $maximo_hour = AdditionalParametersDAO::getValueMax($year,$month,$day,$hour);
//
//              if( $maximo_hour->peak_value_optical_power ){
//
//                writeFile($hour ,$maximo_hour->peak_value_optical_power);
//
//              }




            $hour  = Carbon::createFromFormat('Y-m-d H:i:s', $array_template['event_date'])->format('H');

            $value_max = writeFile($hour,$array_template['element_identifier'],$array_template['txOutputPower']);

            //
            $archivo = base_path().'/' .'xml/parameters_peak.txt';

            if(!$value_max){



              //$maximo_hour = AdditionalParametersDAO::getValueMax($year,$month,$day,$hour,$array_template['element_identifier']);

              // if( $maximo_hour->peak_value_optical_power ){

              //$value_write = $maximo_hour->peak_value_optical_power;

//                if($array_template['txOutputPower'] > $maximo_hour->peak_value_optical_power ){
//
//                  $value_write = $array_template['txOutputPower'];
//                }

//                $valor = $hour . '|' . $array_template['element_identifier'] . '|' . $value_write ;
//
//                writeFileSimple($archivo,$valor);
//
//                $array_template['peak_value_optical_power']  = $value_write;


              //}
              // else{

              $valor = $hour . '|' . $array_template['element_identifier'] . '|' . $array_template['txOutputPower'] ;

              writeFileSimple($archivo,$valor);

              $array_template['peak_value_optical_power']  = $array_template['txOutputPower'];

              //}

            }else{

//                $value_write = $value_max;
//
//                if($array_template['txOutputPower'] > $value_max){
//
//                  $valor = $hour . '|' . $array_template['element_identifier'] . '|' . $array_template['txOutputPower'] ;
//
                  //writeFileSimple($archivo,$array_template['txOutputPower']);
//
//                  $value_write = $array_template['txOutputPower'];
//                }


              $array_template['peak_value_optical_power']  = $value_max;
            }
//dd($hour,$array_template['element_identifier'],$array_template['txOutputPower'],$value_max);
//            if($key == 114){
//
//              dd($hour,$array_template['element_identifier'],$array_template['txOutputPower'],$value_max);
//            }

            break;

          default :

            break;
        }


      }
      $array_template['total_loss_power'] = null;

      $array[$i] = $array_template;
      $i++;
      
      /*if($i==20)
      {
      	dd($array);
      
      }*/
    }

    // $array[$i-1]['peak_value_optical_power']  = $value_max;

//dd('joel');
    $array_save = [];

    if (!empty($array)) {

      foreach ($array as $key => $sub_array) {

        if (!is_null($sub_array['ip'])) {


          $success = false;

          if ($sub_array['puerto_origen'] == $sub_array['port']) {


            $ip = $sub_array['identifierdest'];

            $date = $sub_array['timeCaptured'];

            $group = array_except($array, [$key]);

            if (!empty($group)) {

              foreach ($group as $j => $value) {

                $indice = array_search($ip, $value);

                if ($indice) {

                  if ($sub_array['puerto_destino'] == $value['port']) {

                    $success = true;

                    $date_success = $value['timeCaptured'];

                    $difference = $date_success - $date;

                    if ($difference > 0 && $difference <= 15000) {

                      if ($sub_array['txOutputPower'] >= $value['rxOpticalPower']) {

                        $sub_array['total_loss_power'] = $sub_array['txOutputPower'] - $value['rxOpticalPower'];


                        $array_save[$key] = $sub_array;
                        unset($array[$key]);
                        break;
                      }

                    }

                  }

                }

              }

            }
            else {

              $array_save[$key] = $sub_array;
              unset($array[$key]);

            }

            if (!$success) {

              $array_save[$key] = $sub_array;
            }

          }

          $array_save[$key] = $sub_array;

        }

        $array_save[$key] = $sub_array;
      }

    }
    
    return $array_save;


  }
//ADDITION PARAMETERS END

  public function test_use_service($array_data, $array_template)
  {

    $array = [];
    $i = 0;
    $periodictTime = 0;
    echo '<pre>';
    print_r($array_data);

    $string_in = collect($array_data)->implode('monitoredObjectSiteId', ',');

    $string_in = explode(',', $string_in);

    $nodo_parent_name = ElementDAO::getNodoParentIp($string_in);

    foreach ($array_data as $key => $value) {

      $encontro = false;

      foreach ($nodo_parent_name as $k => $v) {

        if ($value['monitoredObjectSiteId'] == $v->ip) {

          $encontro = true;
//          $data[$key]['puerto_origen'] = $v->puerto_origen;
//          $data[$key]['puerto_destino'] = $v->puerto_destino;
//          $data[$key]['identifierdest'] = $v->identifierdest;
        }

      }
      if (!$encontro) {

        unset($array_data[$key]);
      }

    }

    //dd($array_data);

    foreach ($array_data as $key => $value) {

      $displayname = false;

      ksort($value);

      foreach ($value as $k => $v) {

        if($k =='displayedName'){

          $port = substr($v, 0, 4);

          if( $port =="Port" ){

            $displayname = true;

          }
        }

        if($displayname){
          switch ($k) {

            case 'monitoredObjectSiteName' :

              $array_template['element_identifier'] = $v;

              break;

            case 'periodicTime' :

              $periodictTime = $v;
              $array_template['periodic_time'] = $v;

              break;
            //el periodictTime trae valor cero
            //case 'receivedTotalOctetsPeriodic' :
            case 'receivedTotalOctets' :

//            if ($periodictTime) {
//
//              $array_template['bw_received'] = ($v / $periodictTime);// / 1000 para ser Kilobyte se hace en la query
//            }
              $array_template['bw_received'] = $v/1024;

              break;

            case 'receivedTotalOctetsPeriodic' :

//            if ($periodictTime) {
//
//              $array_template['bw_received'] = ($v / $periodictTime);// / 1000 para ser Kilobyte se hace en la query
//            }
              $array_template['received_total_octets_periodic'] = $v;

              break;

            case 'timeCaptured' :

              $fecha_minute = getDateMinStartMinEnd($v, $periodictTime);
              $array_template['traffic_date'] = $fecha_minute['date_start_format'];
              $array_template['minute_start'] = $fecha_minute['minute_start'];
              $array_template['minute_end'] = $fecha_minute['minute_end'];

              break;
            //el periodictTime trae valor cero
            //case 'transmittedTotalOctetsPeriodic' :
            case 'transmittedTotalOctets' :

//            if ($periodictTime) {
//
//              $array_template['bw_transmition'] = ($v / $periodictTime);// /1000 para ser Kilobyte se hace en la query
//            }
              $array_template['bw_transmition'] = $v/1024 ;

              break;
            case 'transmittedTotalOctetsPeriodic' :

//            if ($periodictTime) {
//
//              $array_template['bw_transmition'] = ($v / $periodictTime);// /1000 para ser Kilobyte se hace en la query
//            }
              $array_template['transmitted_total_octets_periodic'] = $v ;

              break;
            default :

              break;
          }
        }


      }

      $array[$i] = $array_template;
      $i++;
    }

dd($array);
    //echo '<pre>';
    //print_r($array);
    //dd('antes de guardar ');

    return $array;

  }


  public function useOfServiceWstest()
  {

     
    saveFileLog('xml/uso_de_servicio.txt', '');

    $dates = fecha_start_end();
    $dates['date_start']='1538039760000';
    $dates['date_end']='1538039819000';  

    $data = get_data_service('getStringXmlUseOfService', $dates);

    $save = $this->processDataUseOfService($data);

    $file = ['xml/uso_de_servicio.txt' => true];
  
    $data=$this->storeReportWstest2($save, 'Traffic', $file);

    /*insertdatatxtuseofservice($data,$dates['date_start']);*/


       
        
  }



   public function storeReportWstest2($data, $model, $file = false)
  {
    $path_entity = CronController::ENTITY_PATH . $model;

    DB::beginTransaction();

    try {

      foreach ($data as $key => $value) {

        $store = $path_entity::create($value);
        $data[$key]['id']=$store->id;
      }
    
      echo "<pre>";
      print_r($data);
      dd();

      if ($file) {

        foreach ($file as $key => $value) {

          saveFileLog($key, $value);
        }

      }
      return $data;
    } catch (\Exception $e) {

      DB::rollBack();

    }

  }





}