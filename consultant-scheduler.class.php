<?php
/*
Retrieve consultant available times
Change consultant available times
Find consultant available time based on time range and timezone and time slot
Block out a time slot


format patern(JSON)
'consultant_id'=>0,
'month'=>[1,2,3,4,5,6,7,8,9,10,11,12],
'days'=[
    '0'=>[['begin'=>1800,
              'end'=>2000,
              'slot_length'=>30]
          ],
    '5'=>[['begin'=>1030,
              'end'=>1430,
              'slot_length'=>30]
          ],
]


//SQL Script
CREATE TABLE `booking` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `consultant_id` INT NULL,
  `booking` DATETIME NULL,
  `booking_user` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `consultant_id` (`consultant_id` ASC));


*/
class Scheduler{

    private static  $booking_table = "booking";
    private static  $key_user = "schedule_template";

    /*
     * get array of all slots of consultant
     * params:
     * $consultant_id  - id of consultant
     * $date_from - date from (format 'Ymd')
     * $date_to   - date to (format 'Ymd')
     *
     * return:
     * array of all sllots (inc. booked), key is daye slot (format 'YmdHis'), value - id user booked
    */
    public function getConsultantAllSlots($consultant_id, $date_from, $date_to,$slot_length){
        $aSchedule=[];
        $tablePatern = $this->buildSlots($consultant_id,array('from'=>$date_from,'to'=>$date_to),false,$slot_length);

        ksort($tablePatern);

        return $tablePatern;
    }

    /*
     * get array of avable slots of consultant
     * params:
     * $consultant_id  - id of consultant
     * $date_from - date from (format 'Ymd')
     * $date_to   - date to (format 'Ymd')
     *
     * return:
     * array of booked sllots, key is daye slot (format 'YmdHis'), value - id user booked
    */
    public function getConsultantAvaibleSlots($consultant_id, $date_from="", $date_to="",$slot_length=30){
        $aRes=[];
        $tablePatern = $this->buildSlots($consultant_id,array('from'=>$date_from,'to'=>$date_to),true,$slot_length);
        foreach($tablePatern as $slot){
            if (!$slot['busy']){
               $aRes[]=$slot;
            }
        }
        return $aRes;
    }

    /*
     * get array of booked slot
     * params:
     * $consultant_id  - id of consultant
     * $date_from - user boocked
     * $date_to   - date booking, format YmdHis
     *
     * return:
     * array of booked sllots, key is daye slot (format 'YmdHis'), value - id user booked
    */
    public function getConsultantBookingSlots($consultant_id, $date_from, $date_to){
        global $wpdb;
        $aRes=[];
        $ar = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM  ".$wpdb->prefix."booking
        WHERE consultant_id = %d AND booking>=%s AND booking<=%s", $consultant_id,$date_from,$date_to ));
        // $ar=[
        //     ['id'=>1,'booking'=>'20180804103000','booking_user'=>2],
        //     ['id'=>1,'booking'=>'20180806180000','booking_user'=>3],
        //     ['id'=>1,'booking'=>'20180827200000','booking_user'=>4],
        // ];

        foreach($ar as $slot){


            $aRes[]=['user'=>$slot->booking_user,
                     'from'=> DateTime::createFromFormat('Y-m-d H:i:s',$slot->booking),
                     'to'  => DateTime::createFromFormat('Y-m-d H:i:s',$slot->booking)->modify('+'.($slot->slot_length-1).' minutes'),
                     'id'  =>$slot->id];
        }
        return $aRes;
    }


    public function getConsultantBookedSlots($consultant_id, $date_from, $date_to){
        $arBooked = $this->getConsultantBookingSlots($consultant_id, $date_from, $date_to);
        $res = [];
        foreach ($arBooked as $book){
            $res[]=['from'=>$book['from']->format('Y:m:d H:i:s'),
                    'to'=>$book['to']->format('Y:m:d H:i:s'),
                    'user'=>$book['user'],
                     'id'=>$book['id']];
        }
        return $res;
    }

    /**
     * booking slot
     * params:
     * $consultant_id  - id of consultant
     * $userbooking_id - user boocked
     * $date_booking   - date booking, format YmdHis
     *
     * return
     *  - array with key 'status' (ok or err) and key: "message" (error description) or "slot_id' ( new id of booked slot)
    */
    public function bookingSlot($consultant_id,$userbooking_id, $date_booking,$slot_length=30){
        global $wpdb;

        $dt = DateTime::createFromFormat('YmdHis',$date_booking);
        $tablePatern = $this->buildSlots($consultant_id,array('from'=>$dt->format('Ymd'),'to'=>$dt->format('Ymd')),false,$slot_length);

        if ($this->checkSlots($tablePatern,['from'=>$dt,'to'=>DateTime::createFromFormat('YmdHis',$date_booking)->modify('+ '.$slot_length.'minutes')])){
            $res = $wpdb->insert($wpdb->prefix."booking",array('consultant_id'=>$consultant_id,'booking'=>$date_booking,'booking_user'=>$userbooking_id,'slot_length'=>$slot_length));
            return array('status'=>'ok','slot_id'=>$wpdb->insert_id);
        }else {
            return array('status'=>'err','message'=>'slot is busy');
        }
    }
    /*
     * unbooking slot
     * params:
     * consultant_id - consultant id
     * slot_id - id bokked slot
     *
     * return
     * if success TRUE else FALSE
     */
    public function unBookingSlot($consultant_id, $slot_id){
        global $wpdb;
        $slot = $wpdb->query( $wpdb->prepare(
			"DELETE FROM ".$wpdb->prefix."booking
			  WHERE consultant_id=%d AND id=%d",$consultant_id,$slot_id) );
        if ($slot) {
            return true;
        } else {
            return false;
        };

    }

    /*
     * return avaible consultant for given slots
     * params:
     * arrSlots - array of slots, format 'YmdHis'
     *
     * return:
     * array of avaible consultant id
     */
    public function getAvaibleConsultants($arrSlots,$slot_length=30){
        asort($arrSlots);
        $date_from = DateTime::createFromFormat('YmdHis',$arrSlots[0]);
        $date_to =  DateTime::createFromFormat('YmdHis',$arrSlots[count($arrSlots)-1]);

        $avaibleConsultants=[];
        $users = get_users(array());
        foreach($users as $user){
            //||user_can($user['ID'] ,'contributor')
            if (true) {
                $aSlots = $this->buildSlots($user->ID,array('from'=>$date_from->format('Ymd'),'to'=>$date_to->format('Ymd')),false,$slot_length);
                if ($this->checkSlots($aSlots,['from'=>$date_from,'to'=>$date_to]))
                   $avaibleConsultants[]=$user->ID;
            }

        }
         return $avaibleConsultants;
    }

    /*
     * build  slots list consultant's  patern
     * params:
     * consultant_id - consultant id,
     * aInterval     - array with key 'from' and 'to' - date (format 'Ymd') interval for duilt list
     * onlyFree      - include in list on free slots, default FALSE
     *
     * return:
     * array of array consultant's slot (keys: 'date' (format'YmdHis'),
     *        'length' (length od slot in minutes),  'busy' ( slot was booked ot not)
     */
    public function buildSlots($consultant_id,$aInterval,$onlyFree=false,$slot_length=30){
        $patern = get_user_meta($consultant_id,'schedule_template',false);
        $aPatern = @unserialize($patern[0]);

        $cotz = get_field("timezone","user_".$consultant_id);

        $consultant_dtz = new DateTimeZone($cotz);
        $consultant_dt = new DateTime("now", $consultant_dtz);

        $origin_dtz = new DateTimeZone('UTC');
        $origin_dt = new DateTime("now", $origin_dtz);

        $offset = $consultant_dtz->getOffset($consultant_dt) / 3600;
        $offset = $offset > 0 ? "-".abs($offset) : "+".abs($offset);

        if (is_array($aPatern)){
            $slots=[];
            $fromDate = new DateTime($aInterval['from']);
            $toDate = new DateTime($aInterval['to']);
            $aBooking = $this->getConsultantBookingSlots($consultant_id,$fromDate->format('Ymd').'000000',$toDate->format('Ymd').'235959');
            $aExcludeDate = unserialize(get_the_author_meta('schedule_exclude_date',$consultant_id ));
            $aSingleDates=unserialize(get_the_author_meta('schedule_single_dates',$consultant_id ));
            if (!is_array($aExcludeDate)) $aExcludeDate=[];
            if (!is_array($aSingleDates)) $aSingleDates=[];

            while($fromDate<=$toDate){

                $month = $fromDate->format('m');
                $day = $fromDate->format('w');

                //check for day and exclude date
                if (isset($aPatern[$day-1])&&array_search($fromDate->format('Y-m-d'),$aExcludeDate)===FALSE){
                    //fill timeslots
                    if(isset($aPatern[$day-1]['slots'])):
                    foreach($aPatern[$day-1]['slots'] as $slot){

                        $fromtime = new DateTime($fromDate->format('Ymd').$slot['begin']);
                        $fromtime->modify($offset." hours");
                        $totime = new DateTime($fromDate->format('Ymd').$slot['end']);
                        $totime->modify($offset." hours");

                        while($fromtime<=$totime){
                            $endtime = new DateTime($fromtime->format('YmdHis'));
                            $busy=$this->isDateBetweenDates($fromtime,$endtime->modify('+'.$slot_length.' minutes'),$aBooking);
                            if ($onlyFree) {
                                if (!$busy)
                                $slots[$fromtime->format('YmdHis')]=['date'=>$fromtime->format('YmdHis'),'length'=>$slot_length,'busy'=>false];
                            } else {
                                $slots[$fromtime->format('YmdHis')]=['date'=>$fromtime->format('YmdHis'),'length'=>$slot_length,'busy'=>$busy];
                            }

                            $fromtime->modify('+'.$slot_length.' minutes');
                        }

                    }
                    endif;

                }

                $fromDate->modify('+1 day');
            }
            if ($aSingleDates){
                foreach ($aSingleDates as $key=>$sdate){
                    foreach ($sdate['slots'] as $slot) {
                        $fromtime = new DateTime($key.$slot['begin']);
                        $totime = new DateTime($key.$slot['end']);
                        while($fromtime<=$totime){
                            $endtime = new DateTime($fromtime->format('YmdHis'));
                            $busy=$this->isDateBetweenDates($fromtime,$endtime->modify('+'.$slot_length.' minutes'),$aBooking);
                            if ($onlyFree) {
                                if (!$busy)
                                    $slots[$fromtime->format('YmdHis')]=['date'=>$fromtime->format('YmdHis'),'length'=>$slot_length,'busy'=>false];
                            } else {
                                $slots[$fromtime->format('YmdHis')]=['date'=>$fromtime->format('YmdHis'),'length'=>$slot_length,'busy'=>$busy];
                            }
                            $fromtime->modify('+'.$slot_length.' minutes');
                        }
                    }
                }

            }
            return $slots;
        }else {
            return array();
        }

    }

    private function isDateBetweenDates($startDate, $endDate,$aBookedSlots) {
        $res= false;
        foreach($aBookedSlots as $booked) {
            if (($booked['from'] >= $startDate && $booked['from'] < $endDate)||
                ($booked['to'] >= $startDate && $booked['to'] < $endDate)||
                ($booked['from'] <= $startDate&&$booked['to']>$endDate)){
                $res = true ;
                break;
            };
        }
        return $res;
    }


    /*
     * check given array slot for avaible
     * params:
     * arrSlots - array of slots, prepared by BuildSlots
     * date_booking - date of booking, format 'YmdHis', can be array or string
     * return:
     * return TRUE if slots avaible else FALSE
     */
    public function checkSlots($aSlots,$booked){
        $res=false;
        foreach($aSlots as $slot) {
            if (is_array($booked)) {
                if (($booked['from'] >= DateTime::createFromFormat('YmdHis',$slot['date']) && $booked['from'] < DateTime::createFromFormat('YmdHis',$slot['date'])->modify('+ '.$slot['length'].' minutes') )||
                    ($booked['to'] >= DateTime::createFromFormat('YmdHis',$slot['date'])  && $booked['to'] < DateTime::createFromFormat('YmdHis',$slot['date'])->modify('+ '.$slot['length'].' minutes'))||
                    ($booked['from'] <=DateTime::createFromFormat('YmdHis',$slot['date']) &&$booked['to']>DateTime::createFromFormat('YmdHis',$slot['date'])->modify('+ '.$slot['length'].' minutes'))){
                    $res=true;
                    if ($slot['busy']) $res=false;
                    break;
                }
            }
        }
        return $res;
    }

}
