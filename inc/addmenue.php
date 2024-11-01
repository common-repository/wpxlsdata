<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once('function.php');

class wpxlsdata_Admin_Add_Menue extends  wpxlsdata_FUN {
	public function __construct()
	 {


      add_action('admin_menu',array(&$this, 'wpxlsdata_Admin_menue_add'));
      add_action('wp_ajax_wpxlsdata_export',array(&$this,'wp_ajax_wpxlsdata_export'));
      add_action('wp_ajax_edit_data_row',array(&$this,'wp_ajax_edit_data_row'));

    }

    function wp_ajax_edit_data_row()

    {

    $val=sanitize_text_field($_POST["val"]) ; 
    $id_wpxlsdata=intval($_POST["id_wpxlsdata"]) ; 
    $data_col=sanitize_text_field($_POST["data_col"]) ; 
    $data_row=intval($_POST["data_row"]) ; 


    global $wpdb;
    $table=$wpdb->prefix."wpxlsdata_meta";
    $table2=$wpdb->prefix."wpxlsdata";
    $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
    $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
    $tablein=$wpdb->prefix.$wpxlsdata[0]->xlsname;

    $columns=[];
     $ff=[];
     $is_uniq=0;
     $add=0;
     $queryuique="WHERE ";
     foreach ($wpxlsdata_meta as $key => $value) {
      if ($value->isunique==1 && $value->dbcolumn==$data_col) {
           $is_uniq=1;
           if ($value->type_input!="number") {
             
             $queryuique.=$data_col."='".$val."'";

            }
             if ($value->type_input=="number") {
             
             $queryuique.=$data_col."=".$val;

            }
         }
      
     }

     if ($is_uniq==1) {
      
       $isin=$wpdb->get_results("SELECT * FROM $tablein ".$queryuique);

        if (count($isin)==0) {
          $wpdb->update($tablein,[$data_col=>$val],array("idrow"=>$data_row));
        }

     }
     if ($is_uniq==0) 
     {
          $wpdb->update($tablein,[$data_col=>$val],array("idrow"=>$data_row));
     }


     $res["status"]=200 ; 


    echo json_encode($res);

    exit(); 
    }

    function wp_ajax_wpxlsdata_export()

    {
   require_once( 'fastexcel/vendor/autoload.php' );
    $s=intval($_POST["s"]) ; 
    $type_file=sanitize_text_field($_POST["type_file"]) ; 
    $id_wpxlsdata=intval($_POST["id_wpxlsdata"]) ;
    $upload_dir   = wp_upload_dir();
    $dir = "/".date("Y/m")."/";
    $path= $upload_dir['basedir'].$dir ;
    if (!is_dir( $path)) {
       mkdir( $path);
     } 

 //$dp= $s*20000;
 $ds= ($s-1);



  global $wpdb;
  $table=$wpdb->prefix."wpxlsdata_meta";
  $table2=$wpdb->prefix."wpxlsdata";
  $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
  $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
  $tablein=$wpdb->prefix.$wpxlsdata[0]->xlsname;
  $data=$wpdb->get_results("SELECT * FROM $tablein LIMIT $ds,20000 ",ARRAY_A);



$wExcel = new Ellumilel\ExcelWriter();
$header = [];

foreach ($wpxlsdata_meta as $key => $value) {

  if ($value->type_input=="date") {
       $header[$value->dbcolumn] ="YYYY-MM-DD HH:MM:SS";
  }

  if ($value->type_input=="number") {
       $header[$value->dbcolumn] ="integer"; 
  }

  else
  {
       $header[$value->dbcolumn] ="string"; 
  }

  
}
   
$wExcel = new Ellumilel\ExcelWriter();
$wExcel->writeSheetHeader('Sheet1', $header);

foreach ($data as $key => $value) {

  $v=array_values($value);
  unset($v[0]);
 $wExcel->writeSheetRow('Sheet1',$v);

}

$filename=$s."-".date("Y-m-d").".".$type_file;
$wExcel->writeToFile($path."/".$filename);





   $res["filename"]=$filename ; 
   $res["status"]=200 ; 
   $res["url"]=esc_url( $upload_dir['baseurl'].$dir.$filename) ; 


    echo json_encode($res);

    exit();
    }

    
    public function wpxlsdata_Admin_menue_add() 
  {

    global $wpdb;
    $table=$wpdb->prefix."wpxlsdata";
    $wpxlsdata=$wpdb->get_results("SELECT * FROM $table ");

    foreach ($wpxlsdata as $key => $value) {
    
     add_menu_page( 
        $value->xlsname."-".$value->id_wpxlsdata, 
        $value->title, 
        'administrator', 
        'wpxlsdata_Admin_menue_db_show_'.$value->id_wpxlsdata, 
        array(&$this,'wpxlsdata_Admin_menue_db_show'),
        "dashicons-plus-alt"
      );


     add_submenu_page(
        'wpxlsdata_Admin_menue_db_show_'.$value->id_wpxlsdata, 
          $value->xlsname."-".$value->id_wpxlsdata, 
          "Add ".$value->title,
          'administrator',
          'wpxlsdata_add_new_handle_'.$value->id_wpxlsdata,
          array(&$this,'wpxlsdata_add_new_handler'), 
           "dashicons-plus-alt"
         
        );

      add_submenu_page(
        'wpxlsdata_Admin_menue_db_show_'.$value->id_wpxlsdata, 
          $value->xlsname."-".$value->id_wpxlsdata, 
          __("Setting","wpxlsdata"),
          'administrator',
          'wpxlsdata_add_setting_menue_'.$value->id_wpxlsdata,
          array(&$this,'wpxlsdata_add_setting_menue'), 
          "dashicons-plus-alt"
          
        );


       add_submenu_page(
        'wpxlsdata_Admin_menue_db_show_'.$value->id_wpxlsdata, 
          $value->xlsname."-".$value->id_wpxlsdata, 
          __("Import","wpxlsdata"),
          'administrator',
          'wpxlsdata_add_import_menue_'.$value->id_wpxlsdata,
          array(&$this,'wpxlsdata_add_import_menue'), 
           "dashicons-plus-alt"
          
        );


        add_submenu_page(
        'wpxlsdata_Admin_menue_db_show_'.$value->id_wpxlsdata, 
          $value->xlsname."-".$value->id_wpxlsdata, 
          __("Export","wpxlsdata"),
          'administrator',
          'wpxlsdata_add_export_menue_'.$value->id_wpxlsdata,
          array(&$this,'wpxlsdata_add_export_menue'), 
           "dashicons-plus-alt"
          
        );


        add_submenu_page(
        'wpxlsdata_Admin_menue_db_show_'.$value->id_wpxlsdata, 
          $value->xlsname."-".$value->id_wpxlsdata, 
          __("Shortcode","wpxlsdata"),
          'administrator',
          'wpxlsdata_add_shortcode_menue_'.$value->id_wpxlsdata,
          array(&$this,'wpxlsdata_add_shortcode_menue'), 
           "dashicons-plus-alt"
          
        );

    }


 

  }


  function wpxlsdata_Admin_menue_db_show()

  {

    $id_wpxlsdata= intval(explode("-",  $GLOBALS['title'])[1] );
    global $wpdb;
    $table=$wpdb->prefix."wpxlsdata_meta";
    $table2=$wpdb->prefix."wpxlsdata";
    $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
    $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
    $page=1;
    if(isset($_GET['pag']))
     {
      $page=intval($_GET['pag']);
      if ( $page <= 1) {
         $page=1;
      }
    }
    $page=$page-1;
    $offset =intval($page*50);
    $querystate="" ; 


    if ( isset($_GET["p"])) {
      $s=sanitize_text_field($_GET["p"]);
      foreach ($wpxlsdata_meta as $key => $value)

       { 

        if ($key==0) {
             $querystate.=" WHERE $value->dbcolumn LIKE '%{$s}%'  ";
        }
        if ($key > 0) {
             $querystate.=" OR $value->dbcolumn LIKE '%{$s}%'  ";
        }

       }



    }
      $table_name=$wpdb->prefix.$wpxlsdata[0]->xlsname;
      if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)

         {
          echo  '<div id="message" class="notice notice-error"><p>'.__("Please do setting first","wpxlsdata").'</p>
            <a href="'.admin_url().'admin.php?page=wpxlsdata_add_setting_menue_'.$id_wpxlsdata.'" >'.__("Setting","wpxlsdata").' </a>

          </div>';
          return true;

          }




    if (isset($_GET['delete']))
       {
         $wpdb->delete($table_name,array("idrow"=>intval($_GET['delete'])));
    }





    $xlsrows=$wpdb->get_results("SELECT * FROM $table_name ".$querystate." LIMIT $offset,50");
    $count=$wpdb->get_results("SELECT COUNT(idrow) FROM $table_name ");
    $pagenations=intval(get_object_vars($count[0])["COUNT(idrow)"]);


    $page=$page+1;
    $actual_link = (isset($_SERVER['HTTPS'])) ? "https" : "http";
    $actual_link.="://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $actual_link_li= $actual_link; 

    ?>

     <form action="" method="GET" style="float: left">
          <div class="alignleft actions bulkactions" >
          <input type="hidden" value="<?php echo $_GET['page'] ?>" name="page" size="15">
          <input type="text" class="input-xls" placeholder="<?php _e('Search text',"wpxlsdata") ?>" name="p" size="15" >
          <input   class="btnxls left" value="<?php _e('Search',"wpxlsdata") ?> " type="submit">
        </div>
        </form>

     <form  method="GET">
        Paganation: <?php  echo ceil($pagenations/50);?>
        <a class="pagenations" href="<?php echo add_query_arg( 'pag', $page+1, $actual_link_li ); ?>">
          <span aria-hidden="true"><?php echo __("Next","wpxlsdata") ?></span>
        </a>
       
        <input style="width: 40px;" type="text" id="current-page-selector" class="current-page" name="pag" value="<?php echo $page;?>" />
        <a class="pagenations" href="<?php echo add_query_arg( 'pag', $page-1, $actual_link_li ); ?>">
          <span aria-hidden="true"><?php echo __("Prive","wpxlsdata") ?></span>
        </a>
       </form>

    <table  class="tablexls">
      
      <thead>
        <tr>
          
          <th  ><?php _e("Row" ,"wpxlsdata") ?></th>
          <?php 
          foreach ($wpxlsdata_meta as $key => $value) {  ?> 
            <th  class=""><?php echo $value->meta_value ?> </th>
          <?php } ?>
          <th><?php _e("Action","wpxlsdata")?></th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          
          <th  ><?php _e("Row" ,"wpxlsdata") ?></th>
         <?php 
          foreach ($wpxlsdata_meta as $key => $value) {  ?> 
            <th  ><?php echo $value->meta_value ?> </th>
          <?php } ?>
          <th><?php _e("Action","wpxlsdata")?></th>
        </tr>
      </tfoot>
      <tbody>
        <?php  
          $ir= 1 ;
          $j=1;
          foreach ($xlsrows as  $value) {?>

            <tr  >
            
            <td ><?php echo ($page-1)*50+$ir ; ?></td>

            <?php 


             foreach ($wpxlsdata_meta as $k => $val) {  ?> 
             
           
              <td >
                <span class="valwpxlsdata idrow-<?php echo ($j); ?>" data-id="idrow-<?php echo ($j); ?>" > 
                  <?php $kk=$val->dbcolumn; _e($value->$kk); ?>
                    
                  </span>
                    <input 
                     id="idrow-<?php echo ($j); ?>" 
                     type="text" class="none input-edit" 
                     value="<?php _e($value->$kk); ?>"
                     data-db="<?php echo $id_wpxlsdata ?> "
                     data-col="<?php echo $val->dbcolumn ?> "
                     data-row="<?php echo ($value->idrow); ?>" 
                     >
              </td>
               
            <?php 

             $j++;

              }
             ?>

           <td>
            

              <a href="<?php echo $actual_link_li ?>&delete=<?php echo $value->idrow ?>">
               <?php _e("Delete","wpxlsdata")?>
             </a>
           </td>


          </tr>


          <?php $ir++; }?>

      </tbody>
    </table>


        

  <?php }



  function wpxlsdata_add_setting_menue()

  {
     $id_wpxlsdata= intval(explode("-",  $GLOBALS['title'])[1] );
     global $wpdb;
    $table=$wpdb->prefix."wpxlsdata_meta";
    $table2=$wpdb->prefix."wpxlsdata";
    $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
    $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
    $is_ex=0;
    $dbnames=[];

    if (isset($_POST["updatecolumn"])) {

      if ( ! isset( $_POST['xlsupload'] ) || ! wp_verify_nonce( $_POST['xlsupload'], 'wpxlsdata' ) ) {
       $error.='<div id="message" class="notice notice-error"><p>'.__("Nonce error","wpxlsdata").'</p></div>';
       }else{

      $table_name=$wpdb->prefix.$wpxlsdata[0]->xlsname;


      if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name)

         { 
        // $dbnames=$wpdb->get_results("SHOW COLUMNS FROM  $table_name ");
          $is_ex=1;
         }else
         {

            $DB_COLLATE = (empty(DB_COLLATE)) ? " ":"COLLATE ".DB_CHARSET."";
            $DB_CHARSET= (empty(DB_CHARSET)) ? "utf8":DB_CHARSET;
             $sql="CREATE TABLE IF NOT EXISTS `$table_name` (";
                $sql.=" `idrow` int(255) NOT NULL AUTO_INCREMENT,";
             foreach ($wpxlsdata_meta as $key => $value) 
               {

                 switch ($value->type_input) {
                   case 'text':
                     $sql.=" `$value->dbcolumn` varchar(255) CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."   NULL,";
                     break;

                     case 'email':
                     $sql.=" `$value->dbcolumn` varchar(255) CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."  NULL,";
                     break;

                    

                     case 'number':
                     $sql.=" `$value->dbcolumn` int(255) NULL,";
                     break;

                      case 'date':
                     $sql.=" `$value->dbcolumn` date DEFAULT NULL,";
                     break;
                   
                   case 'textarea':
                     $sql.=" `$value->dbcolumn` longtext CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."  NULL,";
                     break;

                 }
               }

            $sql.=" PRIMARY KEY (`idrow`)  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;"; 

            $wpdb->query($sql);


         }


      foreach ($wpxlsdata_meta as $key => $value) 

        {
         $up["type_input"]=sanitize_text_field($_POST["type_input_$key"]); 

         if ($value->type_input!=$up["type_input"]) {

          $sql="";
          $DB_COLLATE = (empty(DB_COLLATE)) ? " ":"COLLATE ".DB_CHARSET."";
          $DB_CHARSET= (empty(DB_CHARSET)) ? "utf8":DB_CHARSET;

          switch ($up["type_input"]) {
                   case 'text':
                     $sql="ALTER TABLE $table_name MODIFY  `$value->dbcolumn`  varchar(255) CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."   NULL";
                     break;

                     case 'email':
                     $sql="ALTER TABLE $table_name MODIFY `$value->dbcolumn`  varchar(255) CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."  NULL";
                     break;

                    

                     case 'number':
                     $sql=" ALTER TABLE $table_name MODIFY  `$value->dbcolumn`  int(255) NULL";
                     break;

                      case 'date':
                     $sql=" ALTER TABLE $table_name MODIFY  `$value->dbcolumn`  date DEFAULT NULL";
                     break;
                   
                   case 'textarea':
                     $sql=" ALTER TABLE $table_name MODIFY  `$value->dbcolumn`  longtext CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."  NULL";
                     break;

                 }

             $ok= $wpdb->query($sql);
            
          }

         $up["meta_value"]=sanitize_text_field($_POST[$value->meta_key]);
         $up["isunique"]= (isset($_POST["unique_".$key])) ? 1:0;
         $wpdb->update($table,$up,array("id_meta"=>$value->id_meta));



        }

    $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);

      if ($is_ex==1) {
              $sql="";
              $DB_COLLATE = (empty(DB_COLLATE)) ? " ":"COLLATE ".DB_CHARSET."";
               $DB_CHARSET= (empty(DB_CHARSET)) ? "utf8":DB_CHARSET;
          foreach ($wpxlsdata_meta as $key => $value) {

           $tt=str_replace(" ","_", trim(strtolower($value->meta_value))); 

            if ($tt!=$value->dbcolumn) {

             
              switch ($value->type_input) {
                   case 'text':
                     $sql="ALTER TABLE $table_name CHANGE  COLUMN  `$value->dbcolumn`  `$tt`  varchar(255) CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."   NULL";
                     break;

                     case 'email':
                     $sql="ALTER TABLE $table_name CHANGE  COLUMN  `$value->dbcolumn`  `$tt` varchar(255) CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."  NULL";
                     break;

                    

                     case 'number':
                     $sql=" ALTER TABLE $table_name CHANGE  COLUMN  `$value->dbcolumn`  `$tt` int(255) NULL";
                     break;

                      case 'date':
                     $sql=" ALTER TABLE $table_name CHANGE  COLUMN  `$value->dbcolumn`  `$tt` date DEFAULT NULL";
                     break;
                   
                   case 'textarea':
                     $sql=" ALTER TABLE $table_name CHANGE  COLUMN  `$value->dbcolumn`  `$tt` longtext CHARACTER SET ".$DB_CHARSET." ".$DB_COLLATE."  NULL";
                     break;

                 }

             $ok= $wpdb->query($sql);
             if ($ok) {
               
                 $upc["dbcolumn"]=$tt;
                 $wpdb->update($table,$upc,array("id_meta"=>$value->id_meta));
             }

            }
          }
         }
       }

    }


 ?> 

     <div class="notice notice-warning is-dismissible">
       <p> 
        <p>Select at least one unique field to prevent duplicate data storage</p>
        <p>The unique field will update the field when re-uploading</p>
        <p>If you do not have any  a unique field, the duplicate data is saved as a new row and added to the database</p>
      </p>
      </div>
     <table  class="tablexls">
      
      <thead>
        <tr>
          <th><?php _e("Row","wpxlsdata")?></th>
          <th><?php _e("Title","wpxlsdata")?></th>
          <th><?php _e("Setting","wpxlsdata")?></th>

        </tr>
      </thead>

  <tbody>
     <form method="POST"> 

      <?php 

      echo wp_nonce_field( "wpxlsdata",  "xlsupload",  false ,  true);
     
      foreach ($wpxlsdata_meta as $key => $value) { ?> 
           
      <tr class="top">
        <td class="titledesc" scope="row"> <?php echo $key+1 ?></td>
        <td>
              <input type="text"  name="<?php echo $value->meta_key ?>" placeholder="Set Title" value="<?php echo $value->meta_value ?>" class="input-xls" />
         </td>
         <td>
          <?php _e("Unique" , "wpxlsdata")?>
           <input type="checkbox" name="unique_<?php echo $key ?>"  <?php if($value->isunique==1) {echo "checked";} ?>>
           <select name="type_input_<?php echo $key ?>">
             <option <?php if($value->type_input=="text") {echo "selected";} ?> value="text">Text</option>
             <option <?php if($value->type_input=="textarea") {echo "selected";} ?> value="textarea">Long Text</option>
             <option <?php if($value->type_input=="number") {echo "selected";} ?> value="number">Number</option>
             <option <?php if($value->type_input=="email") {echo "selected";} ?> value="email">Email</option>
             <option <?php if($value->type_input=="date") {echo "selected";} ?> value="date">Date</option>
           </select>
         </td>
      </tr>
    <?php } ?> 
     
      
      <tr class="top">
        <td class="titledesc" scope="row"><?php _e("Action","wpxlsdata") ?></td>
        <td></td>
        <td>
          <button type="submit" name="updatecolumn"  class="btnxls"><?php _e("Update","wpxlsdata") ?> </button>
        </td>
       </tr>
     </form> 
     </tbody>       
    </table>


    <?php 
  }


  function wpxlsdata_add_new_handler() 

  {
    $id_wpxlsdata= intval(explode("-",  $GLOBALS['title'])[1] );
    global $wpdb;
    $table=$wpdb->prefix."wpxlsdata_meta";
    $table2=$wpdb->prefix."wpxlsdata";
    $message="";
    $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
    $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);

    $postdata=[];
    $postupdate=[];


     if (isset($_POST["addnewrow"])) {
        unset($_POST["addnewrow"]) ; 

        if ( ! isset( $_POST['xlsupload'] ) || ! wp_verify_nonce( $_POST['xlsupload'], 'wpxlsdata' ) ) {
         $message.='<div id="message" class="notice notice-error"><p>'.__("Nonce error","wpxlsdata").'</p></div>';
       }else{
        unset($_POST["xlsupload"]) ; 

        $postdata=[];

        foreach (@$_POST as $key => $value) {
         $postdata[$key] = sanitize_text_field($value); 
        }
        $postupdate=$postdata;

       $queryuique="";

       $ff=[];

        foreach ($wpxlsdata_meta as $key => $value) {
         if ($value->isunique==1) {
           $ff[]=$value;
         }
        }

        if (count($ff) > 0 ) {
        $queryuique="WHERE ";

         foreach ($ff as $key => $value) 
         {
          if ($key==0) {

             if ($value->type_input!="number") {
             
             $queryuique.=$value->dbcolumn."='".$postupdate[$value->dbcolumn]."'";

            }
             if ($value->type_input=="number") {
             
             $queryuique.=$value->dbcolumn."=".$postupdate[$value->dbcolumn];

            }


          }if ($key > 0) 
          {

            if ($value->type_input!="number") {
             
             $queryuique.=" OR ".$value->dbcolumn."='".$postupdate[$value->dbcolumn]."'";

            }
             if ($value->type_input=="number") {
             
             $queryuique.=" OR ".$value->dbcolumn."=".$postupdate[$value->dbcolumn];

            }


          }
               unset($postupdate[$value->dbcolumn]);

         }
         
        }
        

        $tablein=$wpdb->prefix.$wpxlsdata[0]->xlsname;





        if (!empty($queryuique)) {
         
        $isin=$wpdb->get_results("SELECT * FROM $tablein ".$queryuique);

      if (count($isin)==0) {
        $in=$wpdb->insert($tablein,$postdata);
        if ($in) {
          $message='<div id="message" class="updated"><p> '.__("Added Successfuly","wpxlsdata").'  </p></div>';
        }

      }
      if (count($isin) > 0) {
       $up= $wpdb->update($tablein,$postupdate,array("idrow"=>$isin[0]->idrow));
       if ($up) {
          
          $message='<div id="message" class="updated"><p> '.__("Updated Successfuly","wpxlsdata").'  </p></div>';
        }
    }
  }
  if (empty($queryuique)) {    

      $in=$wpdb->insert($tablein,$postdata);
        if ($in) {
          
          $message='<div id="message" class="updated"><p> '.__("Added Successfuly","wpxlsdata").'  </p></div>';
        }

    }



  }

     }

     $table_name=$wpdb->prefix.$wpxlsdata[0]->xlsname;
      if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)

         {
          echo  '<div id="message" class="notice notice-error"><p>'.__("Please do setting first","wpxlsdata").'</p>
            <a href="'.admin_url().'admin.php?page=wpxlsdata_add_setting_menue_'.$id_wpxlsdata.'" >'.__("Setting","wpxlsdata").' </a>

          </div>';
          return true;

          }
   
    
    echo $message;
    ?> 


     <table class="form-table">
     <form method="POST"> 



      <?php 
       echo wp_nonce_field( "wpxlsdata",  "xlsupload",  false ,  true);
      foreach ($wpxlsdata_meta as $key => $value) { ?> 
           
      <tr class="top">
        <th class="titledesc" scope="row"> <?php echo $value->meta_value ?></th>
        <td>
          <?php 

            if ($value->type_input=="textarea") { ?>

                <textarea rows="7" class="input-xls"  name="<?php echo $value->meta_key ?>"></textarea>
              
          <?php   }else { ?>
           <input type="<?php echo $value->type_input ?>"  name="<?php echo $value->dbcolumn ?>" placeholder="<?php echo $value->meta_value ?>" class="input-xls" />
         <?php } ?>
         </td>
      </tr>
    <?php } ?> 
     
      
      <tr class="top">
        <th class="titledesc" scope="row"><?php _e("Action","wpxlsdata") ?></th>
        <td>
          <button type="submit" name="addnewrow"  class="btnxls"><?php _e("Add New","wpxlsdata") ?> </button>
        </td>
       </tr>
     </form>        
    </table>


    <?php 



  }



  function wpxlsdata_add_import_menue()

  {

    $id_wpxlsdata= intval(explode("-",  $GLOBALS['title'])[1] );
     global $wpdb;
    $table=$wpdb->prefix."wpxlsdata_meta";
    $table2=$wpdb->prefix."wpxlsdata";
    $message="";
    $path="";
    $persent=0;
    $step=0;
   if (isset($_POST['importxls'])) {

    if ( ! isset( $_POST['xlsupload'] ) || ! wp_verify_nonce( $_POST['xlsupload'], 'wpxlsdata' ) ) {
       $message.='<div id="message" class="notice notice-error"><p>'.__("Nonce error","wpxlsdata").'</p></div>';
       }


   if (isset( $_FILES['file']) && !empty($_FILES['file']['tmp_name']) ) {


    $mime_type=mime_content_type($_FILES['file']['tmp_name']);
    $Is_xls_file=0;


    if ($mime_type=="application/vnd.ms-excel" || $mime_type=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
      $Is_xls_file=1;

    }
    if ($Is_xls_file==0) {

       $message.='<div id="message" class="notice notice-error"><p> '.__("File Type Must Be xls","wpxlsdata").'</p></div>';
    }


  
  if (empty($message)) 
    {
    if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
      }

      $upload_overrides = array( 'test_form' => false );

      $movefile = wp_handle_upload( $_FILES['file'], $upload_overrides );

      if ( $movefile && ! isset( $movefile['error'] ) ) {
        
          $path= $movefile["file"] ;

         
      } else {
         $message.='<div id="message" class="notice notice-error"><p> '.__("Error File Upload","wpxlsdata").'</p></div>';
      }

    }
  


  if (empty($message)) 
    {
        require_once( 'vendor/autoload.php' );


      
      $mime_type= explode(".", $path) ; 
      $mime_type= end($mime_type) ;
      $reader= []; 
      if ($mime_type=="xls") {
      
      $reader = Asan\PHPExcel\Excel::load($path, function(Asan\PHPExcel\Reader\Xls $reader) {
      $reader->setRowLimit(3);
     });
    }

    if ($mime_type=="xlsx") {
      
      $reader = Asan\PHPExcel\Excel::load($path, function(Asan\PHPExcel\Reader\Xlsx $reader) {
      $reader->setRowLimit(3);
     });
    }


      $count = $reader->count();
      $persent=ceil($count/200);
      $step=100/$persent;
      $path=explode("uploads",  $path) ;
      $path=$path[1];
   

   }
 }
}


   echo ($message.'<form method="POST" action="" enctype="multipart/form-data">

       '.wp_nonce_field( "wpxlsdata",  "xlsupload",  false ,  true).'
            <div class="upload" > 
             <input type="file" name="file" id="file"  />
             </div>
            <button type="submit" name="importxls"  class="btnxls"> '.__("Import","wpxlsdata").' </button>
            
          
      </form> ');


      if (!empty($path)) {
      
     echo ('

       <div id="warning"  class="notice notice-warning"><p> '.__("Importing data Please do not close the window tab","wpxlsdata").'</p></div>

      <div id="startimport" >
        <div id="messageimport" class="updated none">
           <p id="added"> '.__("Added ","wpxlsdata").' </p>
           <p id="updated"> '.__("Updated ","wpxlsdata").'   </p>
        </div>
      </div>


      <div class="meter" data-p="'.$persent.'"  data-s="'.$step.'" data-path="'.$path.'" data-id="'.$id_wpxlsdata.'">
         <span style="width: 0%"></span>
       </div>

      ');
      } 



  }

 

 function wpxlsdata_add_export_menue()

 {

  
  $id_wpxlsdata= intval(explode("-",  $GLOBALS['title'])[1] );
  global $wpdb;
  $table=$wpdb->prefix."wpxlsdata_meta";
  $table2=$wpdb->prefix."wpxlsdata";
  $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
  $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
  $tablein=$wpdb->prefix.$wpxlsdata[0]->xlsname;

   $count=$wpdb->get_results("SELECT COUNT(idrow) FROM $tablein ");
   $pagenations=intval(get_object_vars($count[0])["COUNT(idrow)"]);
   $step=ceil($pagenations/20000);
 

      
     _e('

      <p> '.__("Please select format type").':</p>

           '.wp_nonce_field( "wpxlsdata",  "xlsupload",  false ,  true).'
        <p><input type="radio" class="typefile" name="typefile" value="xls" checked> xls</p>
        <p><input type="radio" class="typefile" name="typefile" value="xlsx"> xlsx</p>



        <button class="btnxls" id="exportdata" >'.__("Export data","wpxlsdata").' </button>

        <div id="linkexports" class="none" ></div>


       <div class="meter none"   data-s="'.$step.'"  data-id="'.$id_wpxlsdata.'">
         <span style="width: 0%"></span>
       </div>

      ');
      

 }

 function wpxlsdata_add_shortcode_menue() 


 {

   $id_wpxlsdata= intval(explode("-",  $GLOBALS['title'])[1] );
  global $wpdb;
  $table=$wpdb->prefix."wpxlsdata_meta";
  $table2=$wpdb->prefix."wpxlsdata";
  $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
  $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
  $table=$wpdb->prefix."wpxlsdata_shortcode";
    $Shortcodes=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
 
?>

<p></p>
<p></p>

<div  class="modalces">
    <div class="modalces-content "> 
      <span class="closeces">×</span>

      <div  class="modalcesheadr">
        <label><?php echo __("Title","wpxlsdata") ; ?></label>
        <input type="text" id="titleshortcode">

        <label><?php echo __("Limit row","wpxlsdata") ; ?></label>
        <input type="number" id="limitrow" value="100">

        <button id="saveshortcode" class="btnxls"><?php _e("Save" ,"wpxlsdata") ?></button>
     </div>

      <ul class="ulwpxlsdata">

         <li>
          
          <input type="checkbox" value="row" class="checkboxrows">
          <?php echo __("Row","wpxlsdata") ; ?>
        </li>

      <?php 

      foreach ($wpxlsdata_meta as $key => $value) { ?>
        <li>
         
          <input type="checkbox" value="<?php echo $value->dbcolumn;?>"  checked  class="checkboxrows">
           <?php echo $value->meta_value;?>
        </li>
     <?php  } ?>


   </ul>


 </div>
 </div>
<input type="hidden" id="idwpxlsdata" value="<?php echo $id_wpxlsdata ?>">
<input type="hidden" id="counts" value="<?php echo count($Shortcodes) ?>">

<button id="newshortcode" class="btnxls"> <?php _e("Add New" ,"wpxlsdata") ?> </button>
 <table class="wp-list-table widefat fixed posts">
      
      <thead>
        <tr>
          <th ><?php _e("Row" ,"wpxlsdata") ?></th>
          <th ><?php _e("Title" ,"wpxlsdata") ?>  </th>
          <th ><?php _e("Shortcode" ,"wpxlsdata") ?> </th>
          <th ><?php _e("Action" ,"wpxlsdata") ?> </th>
        </tr>
      </thead>

        <tfoot>
       <tr>
          <th ><?php _e("Row" ,"wpxlsdata") ?></th>
          <th ><?php _e("Title" ,"wpxlsdata") ?>  </th>
          <th ><?php _e("Shortcode" ,"wpxlsdata") ?> </th>
          <th ><?php _e("Action" ,"wpxlsdata") ?> </th>
        </tr>
      </tfoot>


      <tbody id="trtabel">
        
            <?php 
            foreach ($Shortcodes as $key => $value) { ?>
            <tr class="del-<?php echo $value->id_shortcode ?>">
            <td  ><?php echo ($key+1) ?></td>
            <td  ><?php echo $value->title ?> </td>
            <td  > <?php echo $value->shortcode ?></td>
            <td>
             <button class="deletes btns btndanger" data-id="<?php echo $value->id_shortcode ?>"><?php _e("Delete","wpxlsdata")?></button>
            </td>
            </tr>
          <?php } ?>
        


         
      </tbody>

    

    </table>



<?php  }


	
	

}
new wpxlsdata_Admin_Add_Menue() ;