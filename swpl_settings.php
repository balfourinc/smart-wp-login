<?php
/**
 * Options for Smart WP Login
 *
 * @author Nishant Kumar
 */


//No direct access allowed.
if(!function_exists('add_action')){
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    echo 'Get Well Soon. :)';
    exit();
}

class SWPL_Settings {
    
    private static $settings;
    
    /**
     * Constants for retrieveing custom strings
     */
    //Login
    const STRING_LOG_EMPTY_BOTH_FIELDS = 'swpl_string_log_empty_both_fields';
    const STRING_LOG_EMPTY_EMAIL = 'swpl_string_log_empty_email';
    const STRING_LOG_INVALID_EMAIL = 'swpl_string_log_invalid_email';
    const STRING_LOG_EMPTY_PASSWORD = 'swpl_string_log_empty_password';
    const STRING_LOG_INVALID_EMAIL_PASSWORD = 'swpl_string_log_invalid_email_password';
    
    //Register
    const STRING_REG_EMPTY_EMAIL = 'swpl_string_reg_empty_email';
    const STRING_REG_INVALID_EMAIL = 'swpl_string_reg_invalid_email';
    const STRING_REG_REGISTERED_EMAIL = 'swpl_string_reg_registered_email';
    const STRING_REG_LOG_REGISTRATION_COMPLETE = 'swpl_reg_log_registration_complete';
    
    //Retrieve Password
    const STRING_RP_EMPTY_EMAIL = 'swpl_string_rp_empty_email';
    const STRING_RP_NO_USER_REGISTERED = 'swpl_string_rp_no_user_registered';
    const STRING_RP_INVALID_EMAIL = 'swpl_string_rp_invalid_email';
    const STRING_RP_NOTIFICATION = 'swpl_string_rp_notification';
    const STRING_RP_LOG_CHECK_EMAIL = 'swpl_string_rp_log_check_email';
        
    public function __construct() {
        
        self::$settings = $this->initializeSettings();
        
        //Load CSS
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueStyle'));
        //Add settings menu
        add_action('admin_menu', array($this, 'constructMenu'));  
        //Register Settings
        add_action('admin_init', array($this, 'registerSettings'));
    }
    /**
     * Creates default settings, if no settings exist at all.
     * @return array swpl_settings
     */
    private function initializeSettings(){
            
        //Retrieve settings, for updation or returning back.
        $swpl_settings = get_option('swpl_settings');
        
        if(false === $swpl_settings){ //create default settings
        
                // no 1.0, create default settings
                if(SWPL_VERSION != get_option('swpl_version')){

                    update_option('swpl_version', SWPL_VERSION);
                }

                //default message set
                $swpl_settings = array(
                    self::STRING_LOG_EMPTY_BOTH_FIELDS                      => '<strong><i>Error</i></strong>: Both fields are required.',
                    self::STRING_LOG_EMPTY_EMAIL                            => '<strong><i>Error</i></strong>: The email field is empty.',
                    self::STRING_LOG_INVALID_EMAIL                          => '<strong><i>Error</i></strong>: The email you entered is invalid.',
                    self::STRING_LOG_EMPTY_PASSWORD                         => '<strong><i>Error</i></strong>: The password field is empty.',
                    self::STRING_LOG_INVALID_EMAIL_PASSWORD                 => '<strong><i>Error</i></strong>: Either the email or password you entered is invalid.',
                    self::STRING_REG_EMPTY_EMAIL                            => '<strong><i>Error</i></strong>: Please type your e-mail address.',
                    self::STRING_REG_INVALID_EMAIL                          => '<strong><i>Error</i></strong>: The email address isn’t correct.',
                    self::STRING_REG_REGISTERED_EMAIL                       => '<strong><i>Error</i></strong>: This email is already registered, please choose another one.',
                    self::STRING_REG_LOG_REGISTRATION_COMPLETE              => 'Registration complete. Please check your e-mail.',
                    self::STRING_RP_EMPTY_EMAIL                             => '<strong><i>Error</i></strong>: You must provide your email.',
                    self::STRING_RP_NO_USER_REGISTERED                      => '<strong><i>Error</i></strong>: No user is registered with given email.',
                    self::STRING_RP_INVALID_EMAIL                           => '<strong><i>Error</i></strong>: Email is invalid.',
                    self::STRING_RP_NOTIFICATION                            => '<strong><i>Error</i></strong>: Please enter your email address. You will receive a link to create a new password via email.',
                    self::STRING_RP_LOG_CHECK_EMAIL                         => 'Check your e-mail for the confirmation link.',
                );

                //compatibility with old 0.9, check prev. stored options.
                $swpl_0_9['swpl_l'] = get_option('swpl_l');
                $swpl_0_9['swpl_r'] = get_option('swpl_r');
                $swpl_0_9['swpl_rp'] = get_option('swpl_rp');
                
                //no 0.9 options found
                if(!$swpl_0_9['swpl_l'] &&
                    !$swpl_0_9['swpl_r'] &&
                    !$swpl_0_9['swpl_rp'] ){                    
                    $swpl_settings['swpl_enable_login'] = 'on';
                    $swpl_settings['swpl_enable_registration'] = 'on';
                    $swpl_settings['swpl_enable_retrieve_password'] = 'on';
                }else{ //0.9 options are there.
                    if($swpl_0_9['swpl_l']){
                        $swpl_settings['swpl_enable_login'] = 'on';
                    }

                    if($swpl_0_9['swpl_r']){
                        $swpl_settings['swpl_enable_registration'] = 'on';
                    }

                    if($swpl_0_9['swpl_rp']){
                        $swpl_settings['swpl_enable_retrieve_password'] = 'on';
                    }
                }

                update_option('swpl_settings', $swpl_settings);
            }
            
        //return settings
        return $swpl_settings;
    }
    
    
    /**
     * Adds menu item to WordPress admin menu
     */
    public function constructMenu(){
        add_options_page(
                __('Smart WP Login', 'smart-wp-login'), 
                'Smart WP Login', 
                'manage_options', 
                'smart-wp-login',
                array($this, 'smartSettings'));
    }
    
    /**
     * Renders the settings page
     */
    public function smartSettings(){
?>
<div class="wrap">
    <div class="swpl-plug-page">
        <div class="content alignleft">
            <h2>Smart WP Login</h2>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('swpl_settings');
                    do_settings_sections('smart-wp-login'); 
                ?>
                <input type="submit" class="button button-primary" value="<?php _e('Save', 'smart-wp-login'); ?>">
            </form>
        </div>
        <div class="sidebar alignleft">
            <h2>About</h2>
            <p>Smart WP Login is developed by me, Nishant Kumar.</p>
            <p>I am a web developer and like to accept challenges. You can read my latest posts at <a target="_blank" href="http://www.thebinary.in">http://www.thebinary.in</a></p>
            <ul class="follow">
                <li>
                    <a class="facebook" target="_blank" title="Follow me on Facebook" href="https://www.facebook.com/thebinary.in">
                        <img src="<?php echo SWPL_URL.'asset/images/fb-ico.jpg' ?>" alt="Facebook">
                    </a>
                </li>
                <li>
                    <a class="twitter" target="_blank" title="Follow me on Twitter" href="https://twitter.com/9_nishant_kumar">
                        <img src="<?php echo SWPL_URL.'asset/images/twitter-ico.jpg' ?>" alt="Twitter">
                    </a>
                </li>
                <li>
                    <a class="twitter" target="_blank" title="WordPress Profile" href="https://profiles.wordpress.org/nishant_kumar">
                        <img src="<?php echo SWPL_URL.'asset/images/wp-ico.jpg' ?>" alt="WordPress">
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php
    }
    
    /**
     * Configures the Settings API
     */
    public function registerSettings(){
        register_setting('swpl_settings', 'swpl_settings');
        
        //
        // Enable Smartness Section
        //
        add_settings_section('swpl_enable', 
                __('Enable Smartness', 'smart-wp-login'), 
                array($this, 'callback_swplEnableSection'), 'smart-wp-login');
        
        //Enable in Login
        add_settings_field('swpl_enable_login', 
                __('Login', 'smart-wp-login'),
                array($this, 'callback_swplEnableLoginField'),
                'smart-wp-login',
                'swpl_enable');
        
        //Enable in Registration
        add_settings_field('swpl_enable_registration', 
                __('Registration', 'smart-wp-login'),
                array($this, 'callback_swplEnableRegistrationField'),
                'smart-wp-login',
                'swpl_enable');
        
        //Enable in Retrieve Password
        add_settings_field('swpl_enable_retrieve_password', 
                __('Retrieve Password', 'smart-wp-login'),
                array($this, 'callback_swplEnableRetrievePasswordField'),
                'smart-wp-login',
                'swpl_enable');
        
        //
        // Enable Hash Section
        //
        add_settings_section('swpl_hash', 
                __('Hash Usernames', 'smart-wp-login'), 
                array($this, 'callback_swplHashSection'), 'smart-wp-login');
        
        //Enable in Login
        add_settings_field('swpl_enable_username_hash', 
                __("Assign a unique hash for usernames (rather than member's email prefix)", 'smart-wp-login'),
                array($this, 'callback_swplEnableUsernameHashField'),
                'smart-wp-login',
                'swpl_hash');
        
        //
        //Custom Message
        //
        add_settings_section('swpl_string', 
                __('Custom Message', 'smart-wp-login'), 
                array($this, 'callback_swplString'), 
                'smart-wp-login');
        
        //Login: Empty Both Fields
        add_settings_field(self::STRING_LOG_EMPTY_BOTH_FIELDS,
                __('<span class="legend login">L</span> Both Fields Empty:', 'smart-wp-login'), 
                array($this, 'callback_swplStringLogEmptyBothFields'), 
                'smart-wp-login',
                'swpl_string');
        
        //Login: Empty Email
        add_settings_field(self::STRING_LOG_EMPTY_EMAIL,
                __('<span class="legend login">L</span> Empty Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringLogEmptyEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Login: Invalid Email
        add_settings_field(self::STRING_LOG_INVALID_EMAIL,
                __('<span class="legend login">L</span> Invalid Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringLogInvalidEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Login: Empty Password
        add_settings_field(self::STRING_LOG_EMPTY_PASSWORD,
                __('<span class="legend login">L</span> Empty Password:', 'smart-wp-login'), 
                array($this, 'callback_swplStringLogEmptyPassword'), 
                'smart-wp-login',
                'swpl_string');
        
        //Login: Invalid email or password
        add_settings_field(self::STRING_LOG_INVALID_EMAIL_PASSWORD,
                __('<span class="legend login">L</span> Invalid Email or Password:', 'smart-wp-login'), 
                array($this, 'callback_swplStringLogInvalidEmailPassword'), 
                'smart-wp-login',
                'swpl_string');
        
        
        
        //Registration: Empty Email
        add_settings_field(self::STRING_REG_EMPTY_EMAIL,
                __('<span class="legend registration">R</span> Empty Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRegEmptyEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Registration: Invalid Email
        add_settings_field(self::STRING_REG_INVALID_EMAIL,
                __('<span class="legend registration">R</span> Invalid Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRegInvalidEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Registration: Registered Email
        add_settings_field(self::STRING_REG_REGISTERED_EMAIL,
                __('<span class="legend registration">R</span> Registered Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRegRegisteredEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Registration: Registration Complete
        add_settings_field(self::STRING_REG_LOG_REGISTRATION_COMPLETE,
                __('<span class="legend registration">R</span> Registration Complete:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRegLogRegistrationComplete'), 
                'smart-wp-login',
                'swpl_string');
        
        
        
        //Retrieve Password: Empty Email
        add_settings_field(self::STRING_RP_EMPTY_EMAIL,
                __('<span class="legend retrieve-pass">RP</span> Empty Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRpEmptyEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Retrieve Password: Invalid Email
        add_settings_field(self::STRING_RP_NO_USER_REGISTERED,
                __('<span class="legend retrieve-pass">RP</span> No User Registered:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRpNoUserRegistered'), 
                'smart-wp-login',
                'swpl_string');
        
        //Retrieve Password: Invalid Email
        add_settings_field(self::STRING_RP_INVALID_EMAIL,
                __('<span class="legend retrieve-pass">RP</span> Invalid Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRpInvalidEmail'), 
                'smart-wp-login',
                'swpl_string');
        
        //Retrieve Password: Notification
        add_settings_field(self::STRING_RP_NOTIFICATION,
                __('<span class="legend retrieve-pass">RP</span> Notification:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRpNotification'), 
                'smart-wp-login',
                'swpl_string');
        
        //Reset Password & Login: Check Email
        add_settings_field(self::STRING_RP_LOG_CHECK_EMAIL,
                __('<span class="legend retrieve-pass">RP</span> Check Email:', 'smart-wp-login'), 
                array($this, 'callback_swplStringRpLogCheckEmail'), 
                'smart-wp-login',
                'swpl_string');
    }
    
    
    ############################################################################
    # Custom Message Section and Fields
    ############################################################################
    public function callback_swplString(){
?>
        <div> <?php _e('You can also use HTML.', 'smart-wp-login'); ?> </div>
        <div style="text-align:right;">
            <span class="legend login">L</span> <?php _e('Login', 'smart-wp-login'); ?>, 
            <span class="legend registration">R</span> <?php _e('Registration', 'smart-wp-login'); ?>, 
            <span class="legend retrieve-pass">RP</span>  <?php _e('Retrieve Password', 'smart-wp-login'); ?> 
        </div>
<?php   
    }
    
    public function callback_swplStringLogEmptyBothFields(){
        $this->callback_swplStringField(self::STRING_LOG_EMPTY_BOTH_FIELDS);
    }
    
    public function callback_swplStringLogEmptyEmail(){
        $this->callback_swplStringField(self::STRING_LOG_EMPTY_EMAIL);
    }
    
    public function callback_swplStringLogInvalidEmail(){
        $this->callback_swplStringField(self::STRING_LOG_INVALID_EMAIL);
    }
    
    public function callback_swplStringLogEmptyPassword(){
        $this->callback_swplStringField(self::STRING_LOG_EMPTY_PASSWORD);
    }
    
    public function callback_swplStringLogInvalidEmailPassword(){
        $this->callback_swplStringField(self::STRING_LOG_INVALID_EMAIL_PASSWORD);
    }
    
    public function callback_swplStringRegEmptyEmail(){
        $this->callback_swplStringField(self::STRING_REG_EMPTY_EMAIL);
    }
    
    public function callback_swplStringRegInvalidEmail(){
        $this->callback_swplStringField(self::STRING_REG_INVALID_EMAIL);
    }
    
    public function callback_swplStringRegRegisteredEmail(){
        $this->callback_swplStringField(self::STRING_REG_REGISTERED_EMAIL);
    }
    
    public function callback_swplStringRegLogRegistrationComplete(){
        $this->callback_swplStringField(self::STRING_REG_LOG_REGISTRATION_COMPLETE);
    }
    
    public function callback_swplStringRpEmptyEmail(){
        $this->callback_swplStringField(self::STRING_RP_EMPTY_EMAIL);
    }
    
    public function callback_swplStringRpNoUserRegistered(){
        $this->callback_swplStringField(self::STRING_RP_NO_USER_REGISTERED);
    }
    
    public function callback_swplStringRpInvalidEmail(){
        $this->callback_swplStringField(self::STRING_RP_INVALID_EMAIL);
    }
    
    public function callback_swplStringRpNotification(){
        $this->callback_swplStringField(self::STRING_RP_NOTIFICATION);
    }
    
    public function callback_swplStringRpLogCheckEmail(){
        $this->callback_swplStringField(self::STRING_RP_LOG_CHECK_EMAIL);
    }
    
    public function callback_swplStringField($string_key){
        $val = (!empty(self::$settings) && isset(self::$settings[$string_key]))?
                self::$settings[$string_key] : '';
        
?>
<input type="text" id="<?php echo $string_key ?>" name="swpl_settings[<?php echo $string_key ?>]"
       value="<?php echo esc_html($val); ?>" class="regular-text ltr">
<?php        
    }
    
    ############################################################################
    # Enable Smartness Section and Fields
    ############################################################################
    public function callback_swplEnableSection(){
        _e('Use email instead of username to', 'smart-wp-login');
    }
    
    public function callback_swplEnableLoginField(){
?>
        <input type="checkbox" id="swpl_enable_login" name="swpl_settings[swpl_enable_login]" <?php echo (self::isLoginSmart())? 'checked="checked"':''; ?> >
<?php
    }
    
    public function callback_swplEnableRegistrationField(){
?>
        <input type="checkbox" id="swpl_enable_registration" name="swpl_settings[swpl_enable_registration]" <?php echo (self::isRegistrationSmart())? 'checked="checked"':''; ?> >
<?php
    }
    
    public function callback_swplEnableRetrievePasswordField(){
?>
        <input type="checkbox" id="swpl_enable_retrieve_password" name="swpl_settings[swpl_enable_retrieve_password]" <?php echo (self::isRetrievePasswordSmart())? 'checked="checked"':''; ?> >
<?php
    }

    ############################################################################
    # Enable Hash Section and Fields
    ############################################################################
    public function callback_swplHashSection(){
    }
    
    public function callback_swplEnableUsernameHashField(){
?>
        <input type="checkbox" id="swpl_enable_username_hash" name="swpl_settings[swpl_enable_username_hash]" <?php echo (self::areUsernamesHashed())? 'checked="checked"':''; ?> >
<?php
    }
    
    ############################################################################
    # Getters
    ############################################################################
    
    /**
     * Wheather Email enabled login or not
     * @return type
     */
    public static function isLoginSmart(){
        //enable for login or not
        $val = (!empty(self::$settings) && isset(self::$settings['swpl_enable_login']));
        return $val;
    }
    
    /**
     * Wheather Email enabled registration or not
     * @return type
     */
    public static function isRegistrationSmart(){
        //enable for login or not
        $val = (!empty(self::$settings) && isset(self::$settings['swpl_enable_registration']));
        return $val;
    }
    
    /**
     * Wheather Email enabled retrieve password or not
     * @return type
     */
    public static function isRetrievePasswordSmart(){
        //enable for login or not
        $val = (!empty(self::$settings) && isset(self::$settings['swpl_enable_retrieve_password']));
        return $val;
    }
    
    /**
     * Returns custom strings saved by user.
     */
    public static function getString($string_key){
        $val = (!empty(self::$settings) && isset(self::$settings[$string_key]))?
                self::$settings[$string_key] : '';
        
        return $val;        
    }

    /**
     * Wheather usernames are hashed or not
     * @return type
     */
    public static function areUsernamesHashed(){
        return (!empty(self::$settings) && isset(self::$settings['swpl_enable_username_hash']));
    }
    
    ############################################################################
    # Misc
    ############################################################################
    
    public function adminEnqueueStyle($hook){
        wp_register_style('swpl_admin_style', SWPL_URL.'asset/css/swpl_admin.css');
        wp_enqueue_style('swpl_admin_style');
    }
}
