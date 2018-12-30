<?php

add_action('show_user_profile', 'extra_user_profile_fields');
add_action('edit_user_profile', 'extra_user_profile_fields');


function extra_user_profile_fields($user, $is_frontend = false)
{
  if(is_user_logged_in())
  {
    ?>
    <?php if (!$is_frontend) {
        ?>
    <h3><?php _e("Scheduler", "blank"); ?></h3>
  <?php
    } ?>
    <?php if (!$is_frontend) {
        ?>
    <table class="form-table ui very basic collapsing celled table">
    <tr>
        <th><label for="address"><?php _e("Schedule template"); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
        <td>
    <?php
    } else {
        ?>
    <div class="ui top attached tabular menu">
      <a class="item active" data-tab="tfirst"><?php _e("Schedule template"); ?></a>
      <a class="item" data-tab="tsecond"><?php _e("Single date(s)"); ?></a>
      <a class="item" data-tab="tthird"><?php _e("Exclude date(s)"); ?></a>
    </div>

  <?php
    } ?>
            <?php
                $days = [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday'
                ];

    $data = unserialize(get_the_author_meta('schedule_template', $user->ID));
    $exclude_dates = unserialize(get_the_author_meta('schedule_exclude_date', $user->ID));
    $single_dates = unserialize(get_the_author_meta('schedule_single_dates', $user->ID));

    if (empty($exclude_dates)) {
        $exclude_dates = array();
    }
    if (empty($single_dates)) {
        $single_dates = array();
    }

    echo '<div class="ui bottom attached tab segment active" data-tab="tfirst">';

    foreach ($days as $k=>$v) {
        ?>
                    <div class="ui segment blue">
                          <div class="field"><div class="ui toggle checkbox">
                          <input <?php if (isset($data[$k])) {
            echo 'checked';
        } ?> id="cs_day_<?php echo $k; ?>" name="cs_days[]" class="cs_day" type="checkbox" value="<?php echo $k; ?>" />
                          <label for="cs_day_<?php echo $k; ?>"> <?php echo _e($v); ?></label>
                        </div></div><br />
                        <div style="display:<?php if (isset($data[$k])) {
            echo 'block';
        } else {
            echo 'none';
        } ?>;" class="hours_interval_<?php echo $k; ?>">
                            <div class="hours_interval_line_<?php echo $k; ?> ui segments">

                            <?php
                               if (!empty($data[$k]['slots'])) {
                                   echo '<script>';
                                   foreach ($data[$k]['slots'] as $slot) {
                                       ?>
                                        AdminConsultantScheduler.addHourInterval('.hours_interval_line_<?php echo $k; ?>',<?php echo $k; ?>,<?php echo json_encode($slot); ?>);
                                        <?php
                                   }
                                   echo '</script>';
                               } ?>
                            </div>
                            <button  class="ui primary left labeled icon button button-primary " onclick="javascript:return AdminConsultantScheduler.addHourInterval('.hours_interval_line_<?php echo $k; ?>',<?php echo $k; ?>,{});"> <i class="plus icon"></i><?php _e('Add interval') ?></button>
                        </div>
                    </div>
                    <?php
    }
    echo '</div>'; ?>
  <?php if (!$is_frontend) {
        ?>
        </td>
    </tr>
    <tr>
        <th><label for="city"><?php _e("Single date(s)"); ?></label></th>
        <td>
  <?php
    } ?>
        <div class="ui bottom attached tab segment" data-tab="tsecond" >
        <div id="single_dates" >
        <?php
        foreach ($single_dates as $key=>$date) {
            $value = $key;
            $key = str_replace('-', '', $key); ?>
            <div class="cs_single_date_wrapper ui segment">
                <span class="ui input"><input type="date" class="cs_single_date" value="<?php echo  $value ?>" name="cs_single_dates[<?php echo $key ?>]" /></span>&nbsp;
                <button class="ui icon button" onclick="javascript:return AdminConsultantScheduler.removeSingleDate(this);"><i class="trash alternate icon"></i></button>
                <div class="cs_single_date_hours ui segments" id="sd_<?php echo $key; ?>">
                <?php
                    if (!empty($date['slots'])) {
                        echo '<script>';
                        foreach ($date['slots'] as $slot) {
                            ?>
                            AdminConsultantScheduler.addHourInterval('#sd_<?php echo $key; ?>','<?php echo $key; ?>',<?php echo json_encode($slot); ?>);
                            <?php
                        }
                        echo '</script>';
                    } ?>
                </div>
                  <div class="ui segment">
                    <button  class="ui primary left labeled icon button button-primary " onclick="javascript:return AdminConsultantScheduler.addHourInterval('#sd_<?php echo $key; ?>','<?php echo $key; ?>',{});" ><i class="plus icon"></i><?php _e('Add interval') ?></button></div>
            </div>
            <?php
        } ?>
        </div>
        <div class="ui segment">
            <button  class="ui primary left labeled icon button button-primary " onclick="javascript:return AdminConsultantScheduler.addSingleDate()" > <i class="plus icon"></i><?php _e('Add new date') ?></button>
          </div>
          </div>
  <?php if (!$is_frontend) {
            ?>
        </td>
    </tr>
    <tr>
    <th><label ><?php _e("Exclude date(s)"); ?></label></th>
        <td>
    <?php
        } ?>
    <div class="ui bottom attached tab segment"  data-tab="tthird">
        <div id="exclude_dates" >
        <?php
        foreach ($exclude_dates as $date) {
            ?>
            <div class="cs_exclude_date_wrapper ui segment"><span class="ui input"><input value="<?php echo $date; ?>" type="date" class="cs_exclude_date" name="cs_exclude_dates[]" /></span>&nbsp;<button class="ui icon button" onclick="javascript:return AdminConsultantScheduler.removeExcludeDate(this);"><i class="trash alternate icon"></i></button></div>
            <?php
        } ?>
        </div>
        <div class="ui segment"><input onclick="javascript:return AdminConsultantScheduler.addExcludeDate()" title="<?php _e('Add new date') ?>" onclick="javascript:return " type="button" class="ui primary button button-primary" value="Add date" /></div>
            </div>
  <?php if (!$is_frontend) {
            ?>
        </td>
    </tr>
    </table>
<?php
        }

        if ($is_frontend)
        {
          echo '<input type="hidden" name="action" value="scheduler_save_action" />';
          echo '<hr />';
          echo '<div class="ui segment"><button onclick="javascript:return AdminConsultantScheduler.updateData()" class="ui positive left labeled icon button button-primary"><i class="check icon"></i>' . __('Update') .  '</button></div>';
        }


      }
}

add_action('personal_options_update', 'save_extra_user_profile_fields');
add_action('edit_user_profile_update', 'save_extra_user_profile_fields');

function save_extra_user_profile_fields($user_id)
{
    /*
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    */

    if (!empty($_POST['cs_days']) && is_array($_POST['cs_days'])) {
        $template = array();

        foreach ($_POST['cs_days'] as $day) {
            $template[$day] = array();

            if (!empty($_POST['cs_hours'][$day]) && is_array($_POST['cs_hours'][$day])
            && !empty($_POST['cs_minutes'][$day]) && is_array($_POST['cs_minutes'][$day])) {
                $template[$day]['slots'] = array();
                $hours = $_POST['cs_hours'][$day];
                $minutes = $_POST['cs_minutes'][$day];

                foreach ($hours['s'] as $key=>$hour) {
                    $interval = array();
                    $interval['begin'] = $hour . ':' . $minutes['s'][$key];
                    $interval['end'] = $hours['e'][$key] . ':'  . $minutes['e'][$key];
                    $interval['slot_length'] = 30;
                    $template[$day]['slots'][] = $interval;
                }
            }
        }

        update_user_meta($user_id, 'schedule_template', serialize($template));
    } else {
        update_user_meta($user_id, 'schedule_template', '');
    }

    if (!empty($_POST['cs_exclude_dates'])) {
        update_user_meta($user_id, 'schedule_exclude_date', serialize($_POST['cs_exclude_dates']));
    } else {
        update_user_meta($user_id, 'schedule_exclude_date', '');
    }

    if (!empty($_POST['cs_single_dates']) && is_array($_POST['cs_single_dates'])) {
        $single_dates = array();

        foreach ($_POST['cs_single_dates'] as $key=>$day) {
            $single_dates[$day] = array('slots'=>array());
            $hours = $_POST['cs_hours'][$key];
            $minutes = $_POST['cs_minutes'][$key];

            foreach ($hours['s'] as $key=>$hour) {
                $interval = array();
                $interval['begin'] = $hour . ':' . $minutes['s'][$key];
                $interval['end'] = $hours['e'][$key] . ':'  . $minutes['e'][$key];
                $interval['slot_length'] = 30;
                $single_dates[$day]['slots'][] = $interval;
            }
        }


        update_user_meta($user_id, 'schedule_single_dates', serialize($single_dates));
    } else {
        update_user_meta($user_id, 'schedule_single_dates', '');
    }
}


function consultant_scheduler_scripts()
{
    wp_localize_script('jquery', 'consultant_scheduler_titles', array(
        'start_time' => __('Start time'),
        'end_time' => __('End time'),
        'add_interval' => __('Add interval'),
        'data_saved' => __('Data saved successfully')
    ));
    wp_register_script('consultant_scheduler', plugins_url('/js/scripts.js', __FILE__), array( 'jquery' ));
    wp_enqueue_script('consultant_scheduler');
}
add_action('admin_enqueue_scripts', 'consultant_scheduler_scripts');
add_action('wp_enqueue_scripts', 'consultant_scheduler_scripts');
