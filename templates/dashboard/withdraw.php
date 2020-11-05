<?php
/**
 * @package TutorLMS/Templates
 * @version 1.4.3
 */

$earning_sum = tutor_utils()->get_earning_sum();
$min_withdraw = tutor_utils()->get_option('min_withdraw_amount');
$formatted_min_withdraw_amount = tutor_utils()->tutor_price($min_withdraw);

$saved_account = tutor_utils()->get_user_withdraw_method();
$withdraw_method_name = tutor_utils()->avalue_dot('withdraw_method_name', $saved_account);

$user_id = get_current_user_id();
$balance_formatted = tutor_utils()->tutor_price($earning_sum->balance);
$is_balance_sufficient = $earning_sum->balance >= $min_withdraw;
$all_histories = tutor_utils()->get_withdrawals_history($user_id, array('status' => array('pending', 'approved', 'rejected')));

$image_base = tutor()->url.'/assets/images/';
$method_icons = array(
    'bank_transfer_withdraw' => $image_base.'icon-bank.svg',
    'echeck_withdraw' => $image_base.'icon-echeck.svg',
    'paypal_withdraw' => $image_base.'icon-paypal.svg'
);

?>

<div class="tutor-dashboard-content-inner tutor-frontend-dashboard-withdrawal">
    <h4><?php echo __('Withdrawal', 'tutor'); ?></h4>

    <div class="withdraw-page-current-balance">
        <img src="<?php echo $image_base; ?>wallet.svg"/>
        <div>
            <small><?php _e('Current Balance', 'tutor'); ?></small>
            <p>
                <?php
                    if($is_balance_sufficient){
                        echo sprintf( __('You currently have %s %s %s ready to withdraw', 'tutor'), "<strong class='available_balance'>", $balance_formatted, '</strong>' );
                    }
                    else{
                        echo sprintf( __('You currently have %s %s %s and this is insufficient balance to withdraw', 'tutor'), "<strong class='available_balance'>", $balance_formatted, '</strong>' );
                    }
                ?>
            </p>
        </div>

        <div>
            <?php
                if($is_balance_sufficient && $withdraw_method_name){
                    ?>
                            <a class="open-withdraw-form-btn" href="javascript:;"><?php _e( 'Make a withdraw', 'tutor' ); ?></a>					
                    <?php
                }
            ?>     
        </div>
          
            
    </div>
                    
    <div class="current-withdraw-account-wrap withdrawal-preference inline-image-text">
        <img  src="<?php echo $image_base; ?>info-icon-question.svg"/>
        <span>
            <?php 
            $my_profile_url = tutor_utils()->get_tutor_dashboard_page_permalink('settings/withdraw-settings');
            echo $withdraw_method_name ?  __(sprintf('The preferred payment method is selected as %s. ', $withdraw_method_name), 'tutor') : '';
            echo sprintf(__( 'You can change your %s withdrawal preference %s.' , 'tutor'), "<a href='{$my_profile_url}'>", '</a>' );
            ?>
        </span>
    </div>

    <?php
    if ($earning_sum->balance >= $min_withdraw && $withdraw_method_name){
        ?>

        <div class="tutor-earning-withdraw-form-wrap">    
            <div>
                <div class="tutor-withdrawal-op-up-success">
                    <div>
                        <i class="tutor-icon-line-cross close-withdraw-form-btn" data-reload="yes"></i>
                        <br/>
                        <br/>
                        <div style="text-align:center">
                            <img src="<?php echo $image_base; ?>icon-cheers.svg"/>
                            <h3><?php _e('Your withdrawal request has been successfully accepted', 'tutor'); ?></h3>
                            <p><?php _e('Please check your transaction notification on your connected withdrawal method', 'tutor'); ?></p>
                        </div>
                        <br/>
                        <br/>
                    </div>
                </div>
                <div class="tutor-withdrawal-op-up-frorm">
                    <div>
                        <i class="tutor-icon-line-cross close-withdraw-form-btn" ></i>
                        <img src="<?php echo $image_base; ?>wallet.svg"/>
                        <h3><?php _e('Make a Withdrawal', 'tutor'); ?></h3>
                        <p><?php _e('Please enter withdrawal amount and click the submit request button', 'tutor'); ?></p>
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <span><?php _e('Current Balance', 'tutor'); ?></span><br/>
                                        <b><?php echo $balance_formatted; ?></b>
                                    </td>
                                    <td>
                                        <span><?php _e('Selected Payment Method', 'tutor'); ?></span><br/>
                                        <b><?php echo $withdraw_method_name; ?></b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>  
                    </div>
                    <div>
                        <form id="tutor-earning-withdraw-form" action="" method="post">
                            <?php wp_nonce_field( tutor()->nonce_action, tutor()->nonce ); ?>
                            <input type="hidden" value="tutor_make_an_withdraw" name="action"/>
                            <?php do_action('tutor_withdraw_form_before'); ?>
                            <div class="withdraw-form-field-row">
                                <label for="tutor_withdraw_amount"><?php _e('Amount', 'tutor') ?></label>
                                <div class="withdraw-form-field-amount">
                                    <span>$</span> <input type="text" name="tutor_withdraw_amount">
                                </div>
                                <div class="inline-image-text">
                                    <img src="<?php echo $image_base; ?>info-icon-question.svg"/>
                                    <span>Minimum withdraw amount is <?php echo strip_tags($formatted_min_withdraw_amount); ?></span>
                                </div>
                            </div>

                            <div class="tutor-withdraw-button-container">
                                <button class="tutor-btn tutor-btn-secondary close-withdraw-form-btn"><?php _e('Cancel', 'tutor'); ?></button>
                                <button class="tutor-btn" type="submit" id="tutor-earning-withdraw-btn" name="withdraw-form-submit"><?php _e('Withdraw', 'tutor'); ?></button>
                            </div>
                            
                            <div id="tutor-withdraw-form-response"></div>

                            <?php do_action('tutor_withdraw_form_after'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    ?>

    <br/>
    <div class="withdraw-history-table-wrap">
        <div class="withdraw-history-table-title">
            <h4> <?php _e('Withdrawals', 'tutor'); ?></h4>
        </div>

        <?php 
            if (tutor_utils()->count($all_histories->results)){
                ?>
                <table class="withdrawals-history tutor-table">
                    <thead>
                        <tr>
                            <th><?php _e('Withdrawal Method', 'tutor') ?></th>
                            <th><?php _e('Amount', 'tutor') ?></th>
                            <th><?php _e('Date', 'tutor') ?></th>
                            <th><?php _e('Status', 'tutor') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($all_histories->results as $withdraw_history){
                            ?>
                            <tr>
                                <td>
                                    <?php 
                                        $method_data = maybe_unserialize($withdraw_history->method_data); 
                                        $method_key = $method_data['withdraw_method_key'];
                                        $method_title = '';

                                        switch($method_key){
                                            case 'bank_transfer_withdraw' : $method_title=$method_data['account_number']['value']; break;
                                            case 'paypal_withdraw' : $method_title=$method_data['paypal_email']['value']; break;
                                        }
                                    ?>
                                    <div class="inline-image-text is-inline-block">
                                        <img src="<?php echo isset($method_icons[$method_key]) ? $method_icons[$method_key] : ''; ?>"/>
                                        &nbsp; 
                                        <span>
                                            <?php 
                                            echo tutor_utils()->avalue_dot('withdraw_method_name', $method_data); 
                                            echo '<small>', (!empty($method_title) ? '<br/>' : ''), $method_title, '</small>';
                                            ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php echo tutor_utils()->tutor_price($withdraw_history->amount); ?>
                                </td>
                                <td>
                                    <?php
                                    echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($withdraw_history->created_at));
                                    ?>
                                </td>
                                <td>
                                    <span class="tutor-status-text status-<?php echo $withdraw_history->status; ?>">
                                        <?php echo __(ucfirst($withdraw_history->status), 'tutor'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
            }
            else{                
                ?>
                <p><?php _e('No withdrawal yet', 'tutor'); ?></p>
                <?php
            }
        ?>
    </div>
</div>
