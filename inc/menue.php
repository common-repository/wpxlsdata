<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once('function.php');

class wpxlsdata_Admin extends  wpxlsdata_FUN {
	public function __construct()
	 {
	    add_action('admin_menu',array(&$this, 'wpxlsdata_Admin_menue'));
      add_action ( 'admin_enqueue_scripts', array(&$this,'wpxlsdata_admin_scripts'));
      add_action('wp_ajax_wpxlsdata_import',array(&$this,'wp_ajax_wpxlsdata_import'));
      add_action('wp_ajax_wpxlsdata_add_shortcode',array(&$this,'wp_ajax_wpxlsdata_add_shortcode'));

      add_action('wp_ajax_wpxlsdata_delete_shortcode',array(&$this,'wp_ajax_wpxlsdata_delete_shortcode'));



    }

    function wpxlsdata_admin_scripts() 

    {
        wp_register_style('wpxlsdata-css', plugins_url('/css/style.css', __FILE__) );
        wp_enqueue_style( 'wpxlsdata-css' );
        wp_enqueue_script( 'wpxlsdata-js', plugins_url( '/js/wpxlsdata.js', __FILE__ ), array( 'jquery' ));
         wp_localize_script( 'wpxlsdata-js', 'the_in_url', array( 'in_url' => admin_url( 'admin-ajax.php' ) ) ); 

    }

    public function wpxlsdata_Admin_menue() 
  {
  add_menu_page('wpxlsdata','Excel to db', 'administrator', 'wpxlsdata_Admin_menue', array(&$this,'wpxlsdata_Admin_all'),"dashicons-format-aside");
  add_submenu_page('wpxlsdata_Admin_menue',"addExcel","Add excel",'administrator','wpxlsdata_add_new',array(&$this,'wpxlsdata_Admin_menue_add'));

  }


  function wp_ajax_wpxlsdata_delete_shortcode () 
  {
      $res["status"]=200 ; 
      $dataid= intval($_POST["dataid"]);
      global $wpdb;
      $table=$wpdb->prefix."wpxlsdata_shortcode";
      $wpdb->delete($table,array("id_shortcode"=>$dataid));
      echo json_encode($res);
     exit();
  }

  function wp_ajax_wpxlsdata_add_shortcode()
  {
    $res["status"]=200 ; 
    $title= sanitize_text_field($_POST["title"]) ; 
    $rows= sanitize_text_field($_POST["rows"]) ;
    $id_wpxlsdata= intval($_POST["id_wpxlsdata"]);
    $limit= intval($_POST['limitrow']);

    $r=[]; 
    $is_row=0;

    $rows= explode(",", $rows);
    foreach ($rows as $key => $value) {
      if (!empty( $value) && $value!="row") {
        $r[]=$value;
      }
      if ( $value=="row") {
       $is_row=1;
      }
    }

    $s='[wpxlsdata type="tabel" iddb='.$id_wpxlsdata.' limit='.$limit.' isrow='.$is_row.' rows="'.implode(",", $r).'"]';
    $res["s"]=$s ; 
    global $wpdb;
     $table=$wpdb->prefix."wpxlsdata_shortcode";
     $postdata["title"]= $title;
     $postdata["shortcode"]= $s;
     $postdata["id_wpxlsdata"]= $id_wpxlsdata;
     $wpdb->insert($table,$postdata);

    echo json_encode($res);
    exit();
  }


  function wp_ajax_wpxlsdata_import() 

  {

   $s=intval($_POST["s"]) ; 
   $p=intval($_POST["p"]) ; 
   $id_wpxlsdata=intval($_POST["id_wpxlsdata"]) ;
   $upload_dir   = wp_upload_dir();
   $path= $upload_dir['basedir'].sanitize_text_field($_POST["path"]) ; 

  if (!empty($path)) 
    {
     require_once( 'vendor/autoload.php' );

     global $wpdb;
     $table=$wpdb->prefix."wpxlsdata_meta";
     $table2=$wpdb->prefix."wpxlsdata";
     $query="";
     $wpxlsdata_meta=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".$id_wpxlsdata);
     $wpxlsdata=$wpdb->get_results("SELECT * FROM $table2 WHERE id_wpxlsdata=".$id_wpxlsdata);
     $tablein=$wpdb->prefix.$wpxlsdata[0]->xlsname;
     $columns=[];
     $ff=[];
     $up=0;
     $add=0;
     foreach ($wpxlsdata_meta as $key => $value) {
      if ($value->isunique==1) {
           $ff[]=$value;
         }
      
      $columns[]=$value->dbcolumn;
     }




    



     $mime_type= explode(".", $path) ; 
      $mime_type= end($mime_type) ;
      $reader= []; 
      if ($mime_type=="xls") {
      
      $reader = Asan\PHPExcel\Excel::load($path, function(Asan\PHPExcel\Reader\Xls $reader) {});
    }

    if ($mime_type=="xlsx") {
      
      $reader = Asan\PHPExcel\Excel::load($path, function(Asan\PHPExcel\Reader\Xlsx $reader) {});
    }  

         $dp= $s*200;
         $ds= ($s-1)*200;

        $d=[];
        foreach ($reader as $key =>  $row) {
            if ($key > $ds && $key <= $dp ) {
             //$d[]=$row;

              
              $postdata=[];
              foreach ($row as $k => $value) {
                 $postdata[$columns[$k]]=$value;
              }
              $postupdate=$postdata;

              //

       $queryuique="";
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



      if (!empty($queryuique)) {
         
        $isin=$wpdb->get_results("SELECT * FROM $tablein ".$queryuique);

      if (count($isin)==0) {
        $in=$wpdb->insert($tablein,$postdata);
        if ($in) {
          $add++;
        }

      }
      if (count($isin) > 0) {
       $up= $wpdb->update($tablein,$postupdate,array("idrow"=>$isin[0]->idrow));
       if ($up) {
          
         $up++;
        }
    }
  }
  if (empty($queryuique)) {    

      $in=$wpdb->insert($tablein,$postdata);
        if ($in) {
          
         $add++;
        }

    }




              //

              
            }
            
        }



   

   
 }

    $res["status"]=200 ; 
    $res["up"] = $up;
    $res["in"] = $add;

    echo json_encode($res);

    exit();
  }

  function wpxlsdata_Admin_all()

  {
    global $wpdb;
    $table=$wpdb->prefix."wpxlsdata";

    if (isset($_GET["delete"])) {

      $wpxlsdata=$wpdb->get_results("SELECT * FROM $table WHERE id_wpxlsdata=".intval($_GET['delete']));

      if (count($wpxlsdata) == 1) {
       
      $wpdb->delete($table,array("id_wpxlsdata"=>intval($_GET['delete'])));
      $table2=$wpdb->prefix."wpxlsdata_meta";
      $wpdb->delete($table2,array("id_wpxlsdata"=>intval($_GET['delete'])));
      $table_name=$wpxlsdata[0]->db;
      $wpdb->query("DROP TABLE $table_name");
    }

    }



    $wpxlsdata=$wpdb->get_results("SELECT * FROM $table ");

     $actual_link = (isset($_SERVER['HTTPS'])) ? "https" : "http";
    $actual_link.="://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    ?>

    <div class="notice notice-warning is-dismissible">
       <p> 
        <p>When delete row All database deleted so very becurfull  </p>
       
      </p>
      </div>
     <p></p>
     <p></p>

    <table class=" wp-list-table widefat fixed posts">
      
      <thead>
        <tr>
          <th  ><?php _e("Row" ,"wpxlsdata") ?></th>
          <th  class="manage-column column-tags"><?php _e("Title" ,"wpxlsdata") ?>  </th>
          <th  class="manage-column column-tags"><?php _e("Name" ,"wpxlsdata") ?> </th>
          <th  class="manage-column column-tags"><?php _e("Description" ,"wpxlsdata") ?>  </th>
          <th class="manage-column column-tags"> <?php _e("Action","wpxlsdata")?></th>
        </tr>
      </thead>

        <tfoot>
        <tr>
          <th  ><?php _e("Row" ,"wpxlsdata") ?></th>
          <th  class="manage-column column-tags"><?php _e("Title" ,"wpxlsdata") ?>  </th>
          <th  class="manage-column column-tags"><?php _e("Name" ,"wpxlsdata") ?> </th>
          <th  class="manage-column column-tags"><?php _e("Description" ,"wpxlsdata") ?>  </th>
          <th class="manage-column column-tags"> <?php _e("Action","wpxlsdata")?></th>
        </tr>
        </tr>
      </tfoot>


      <tbody>
         <?php  
          $i= 1 ;
          foreach ($wpxlsdata as  $value) {?>

          <tr>
            <td  class="author column-author"><?php echo $i ; ?></td>
            <td  class="author column-author"><?php echo $value->title ; ?>  </td>
            <td  class="author column-author"><?php echo $value->xlsname ; ?> </td>
            <td  class="author column-author"><?php echo $value->description ; ?>  </td>

            <td>
              <a href="<?php echo $actual_link ?>&delete=<?php echo $value->id_wpxlsdata ?>">
               <?php _e("Delete","wpxlsdata")?>
             </a>

            </td>
        </tr>


          <?php $i++; } ?>
      </tbody>

    

    </table>


    <?php 

  }

  function wpxlsdata_Admin_menue_add()
  { 

   global $wpdb;
   $error="";
   $path= "" ;

   if (isset($_POST['addwpxlsdata'])) { 

    $title=sanitize_text_field($_POST['title']);
    $xlsname=sanitize_text_field($_POST['xlsname']);
    $description=sanitize_text_field($_POST['description']);


    if ( ! isset( $_POST['xlsupload'] ) || ! wp_verify_nonce( $_POST['xlsupload'], 'wpxlsdata' ) ) {
       $error.='<div id="message" class="notice notice-error"><p>'.__("Nonce error","wpxlsdata").'</p></div>';
       }



    if (empty( $title)) {
       $error.='<div id="message" class="notice notice-error"><p>'.__("Title Is Empty","wpxlsdata").'</p></div>';
    }

     if (empty( $xlsname)) {
       $error.='<div id="message" class="notice notice-error"><p> '.__("Name Is Empty","wpxlsdata").'</p></div>';
    }

    if ($this->Check_text_english($xlsname)) {
      $error.='<div id="message" class="notice notice-error"><p> '.__("Name Not English Text","wpxlsdata").'  </p></div>';
    }

    if (!isset( $xlsname)) {
       $error.='<div id="message" class="notice notice-error"><p> '.__("Xls File Not Selected","wpxlsdata").'</p></div>';
    }

   $table_name= $wpdb->prefix.$xlsname; 

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name)

     { 
      $error.='<div id="message" class="notice notice-error"><p> '.__("Name Can Not Be ==".$xlsname."== Cahange Name","wpxlsdata").'</p></div>';
     }

  
  if (isset( $_FILES['file']) && !empty($_FILES['file']['tmp_name'])) {


    $mime_type=mime_content_type($_FILES['file']['tmp_name']);
    $Is_xls_file=0;


    if ($mime_type=="application/vnd.ms-excel" || $mime_type=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
      $Is_xls_file=1;

    }
    if ($Is_xls_file==0) {

       $error.='<div id="message" class="notice notice-error"><p> '.__("File Type Must Be xls or xlsx","wpxlsdata").'</p></div>';
    }


  
  if (empty($error)) 
    {
    if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
      }

      $uploadedfile = $_FILES['file'];
      $upload_overrides = array( 'test_form' => false );

      $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

      if ( $movefile && ! isset( $movefile['error'] ) ) {
        
          $path= $movefile["file"] ;
      } else {
         $error.='<div id="message" class="notice notice-error"><p> '.__("Error File Upload","wpxlsdata").'</p></div>';
      }

    }
  }

    if (empty($error)) 
    {
        require_once( 'vendor/autoload.php' );


      


      $datain["title"]=$title ; 
      $datain["xlsname"]=$xlsname ; 
      $datain["description"]=$description ; 

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

    $current =$reader->current();
    $datain["db"]= $wpdb->prefix.$xlsname; 

      $table=$wpdb->prefix."wpxlsdata";
      $in=$wpdb->insert($table,$datain);
      if ($in) {
       $lastid = $wpdb->insert_id;
      $table=$wpdb->prefix."wpxlsdata_meta";

      foreach ($current as $key => $value) {

        $tt=str_replace(" ","_", trim(strtolower($value))); 

        if (!preg_match('/[a-zA-Z]/', $value))
          {
           $tt="column".$key;
          }

        
        $datameta["meta_key"]= $key;
        $datameta["meta_value"]=$value; 
        $datameta["dbcolumn"]= $tt;
        $datameta["id_wpxlsdata"]=$lastid;
        $wpdb->insert($table,$datameta);

       }



          $error.='<div id="message" class="updated"><p> '.__("Added Successfuly","wpxlsdata").'  </p></div>';


      }
       
    }


     
   }


   echo ($error.' <form method="POST" action="" enctype="multipart/form-data">


         '.wp_nonce_field( "wpxlsdata",  "xlsupload",  false ,  true).'

        <table class="form-table">
        <tr class="top">
           <td>
             <label>'.__("Title Menue","wpxlsdata").' </label>
           </td>
          <td>
              <input type="text"  name="title" class="input-xls" >
          </td>
        </tr>

         <tr class="top">
           <td>
             <label>'.__("Name !Must be English","wpxlsdata").' </label>
           </td>
          <td>
              <input type="text"  name="xlsname"  class="input-xls">
          </td>
        </tr>


         <tr class="top">
           <td>
             <label>'.__("Description","wpxlsdata").' </label>
           </td>
          <td>
              <textarea rows="7" name="description" class="input-xls" ></textarea>
          </td>
        </tr>

        <tr class="top">
           <td>
             <label>'.__("Sample xls or xlsx File","wpxlsdata").' </label>
           </td>
          <td>
              <input type="file" name="file" />
          </td>
        </tr>

        <tr class="top">
          
          <td>
          <button type="submit" name="addwpxlsdata"  class="button button-primary"> '.__("Add","wpxlsdata").' </button>
            
           
          </td>
         </tr>
                  
    </table>
      </form>
<iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0"width="100%" height="443" type="text/html" src="https://www.youtube.com/embed/C6Nx21W1TjI?autoplay=0&fs=0&iv_load_policy=3&showinfo=0&rel=0&cc_load_policy=0&start=0&end=0&origin=https://youtubeembedcode.com"></iframe>
      '); 

}


	
	

}
new wpxlsdata_Admin() ;