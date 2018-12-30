<?php

include_once('consultant-scheduler.class.php');

add_action( 'wp_enqueue_scripts', 'cs_ajax_data', 99 );

function cs_ajax_data(){

    wp_register_script( 'consultant_scheduler_public', plugins_url( '/js/public.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'consultant_scheduler_public' );

    wp_localize_script('consultant_scheduler_public', 'cs_ajax',
		array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('cs_ajax_nonce')
		)
	);

}


if( wp_doing_ajax() ){



    add_action('wp_ajax_scheduler_save_action', 'cs_ajax_scheduler_save_action');
    add_action('wp_ajax_nopriv_scheduler_save_action', 'cs_ajax_scheduler_save_action');

    function cs_ajax_scheduler_save_action()
    {
      if(is_user_logged_in())
      {
        $user = wp_get_current_user();
        save_extra_user_profile_fields($user->ID);
      }
      wp_die();
    }




    function cs_ajax_set_times()
    {
        if(is_user_logged_in())
        {
            $scheduler = new Scheduler();
           $res= $scheduler->bookingSlot(1,1,'20180903100000','20180903100000');

            echo json_encode($res);//'55555';
        }

        wp_die();
    }

}


add_action('wp_ajax_scheduler_action', 'cs_ajax_scheduler_action');
add_action('wp_ajax_nopriv_scheduler_action', 'cs_ajax_scheduler_action');
/*
add_filter('scheduler_action','my_scheduler_action');
function my_scheduler_action($var) {

}
*/

function cs_ajax_scheduler_action()
{

    if(is_user_logged_in())
    {
       $scheduler = new Scheduler();

       $cons_id =isset($_POST['cons_id']) ? intval($_POST['cons_id']) : 0;
       $from =  isset($_POST['from']) ? ($_POST['from']) : '0000-00-00';
       $to =  isset($_POST['to']) ? ($_POST['to']) : '0000-00-00';
       $slot_length =  isset($_POST['slot_length']) ? intval($_POST['slot_length']) : 30;

       if ($_POST['scheduler_command']=='get_all_slots'){
           return json_encode($scheduler->getConsultantAllSlots($cons_id,DateTime::createFromFormat('Y-m-d',$from)->format('Ymd'), DateTime::createFromFormat('Y-m-d',$to)->format('Ymd'),$slot_length));
       } elseif ($_POST['scheduler_command']=='get_avaible_slots') {
            return json_encode($scheduler->getConsultantAvaibleSlots($cons_id,DateTime::createFromFormat('Y-m-d',$from)->format('Ymd'), DateTime::createFromFormat('Y-m-d',$to)->format('Ymd'),$slot_length));
       }elseif ($_POST['scheduler_command']=='get_booked_slots') {
            return json_encode($scheduler->getConsultantBookedSlots($cons_id,DateTime::createFromFormat('Y-m-d',$from)->format('Ymd'), DateTime::createFromFormat('Y-m-d',$to)->format('Ymd')));
       }elseif ($_POST['scheduler_command']=='get_avaible_consultants') {
           $arDates = $to =  isset($_POST['slots']) ? $_POST['slots'] : array();
           foreach($arDates as $key=>$curDate) {
               $arDates[$key] = DateTime::createFromFormat('Y-m-d H:i:s',$curDate)->format('YmdHis') ;
           }
           return json_encode($scheduler->getAvaibleConsultants($arDates,$slot_length));
       }elseif ($_POST['scheduler_command']=='book_slot') {
           ///get current user
           $user = wp_get_current_user()  ;
            return json_encode($scheduler->bookingSlot($cons_id,$user->ID,DateTime::createFromFormat('Y-m-d H:i:s',$from)->format('YmdHis') ,$slot_length));
       }elseif ($_POST['scheduler_command']=='unbook_slot') {
            $slot_id =isset($_POST['slot_id']) ? intval($_POST['slot_id']) : 0;
            return json_encode($scheduler->unBookingSlot($cons_id,$slot_id));
       }else{
           return json_encode(['err'=>'Bad command']);
       }  ;
       //get_user_meta( $user_id, $key, $single );
       //echo 'get_times';
    }
    //wp_die();
}
