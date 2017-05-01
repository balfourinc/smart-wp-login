<?php

/**
 * Frontend processing engine
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

class SWPL_Engine{
    
    /**
     * Main Constructor
     */
    public function __construct() {
        
        //Process Login
        if(SWPL_Settings::isLoginSmart()){
            //remove wordpress authentication
            remove_filter('authenticate', 'wp_authenticate_username_password', 20);

            //custom authentication function
            add_filter('authenticate', array($this, 'callback_Authenticate'), 20, 3); 

            //Process Gettext for custom strings
            add_action('login_form_login', array($this,'callback_LoginFormLogin'));  
        } 
        
        
        //Process Register
        if(SWPL_Settings::isRegistrationSmart()){                
            //Assign email to username
            add_action('login_form_register', array($this, 'callback_LoginFormRegister'));

            //Remove error for username, show error for email only.
            add_filter('registration_errors', array($this, 'callback_RegistrationErrors'), 10, 3);

            // Remove error for username in Buddypress
            add_filter('bp_core_validate_user_signup', array($this, 'callback_BPRegistrationErrors'));

            // Ensure a username is assign in BP
            add_filter('bp_signup_validate', array($this, 'callback_BPSignupValidation'));

            if (SWPL_Settings::areUsernamesHashed()) {
                // Hash the username
                add_action( 'bp_core_signup_user', array($this, 'callback_BPFinaliseSignup' ), 10, 5);
            }
            
            // Ensure any missing _POST fields are present
            add_action( 'bp_signup_pre_validate', array($this, 'callback_BPPreSignup'));

            /**
             * Add customization to registration form.
             * Ref: 
             * http://codex.wordpress.org/Customizing_the_Login_Form
             * http://codex.wordpress.org/Function_Reference/wp_enqueue_script
             * 
             */
            //>3.0.1 only
            //add_action('login_enqueue_scripts', array($this, 'swplLoginEnqueueScripts'));            
            add_action('login_head', array($this, 'callback_LoginHead'));           
            add_action('login_footer', array($this, 'callback_LoginFooter'));
        }
        
        
        //Process Retrieve Password
        if(SWPL_Settings::isRetrievePasswordSmart()){
            add_action('login_form_lostpassword', array($this, 'callback_LoginFormLostPassword'));
            add_action('login_form_retrievepassword', array($this, 'callback_LoginFormLostPassword'));
        }
    }
    
    ############################################################################
    #  Login With Email
    ############################################################################
    
    /**
     * Custom Authentication function
     */
    public function callback_Authenticate($user, $email, $password){

        //Check for empty fields
        if(empty($email) || empty ($password)){        
            //create new error object and add errors to it.
            $error = new WP_Error();
            
            /*
             * Added v1.0
             * Don't know why, but WP doesn't show an error when both fields are empty.
             * Thats why we are making it smart.
             */
            if('post' === strtolower($_SERVER['REQUEST_METHOD'])){
                if(empty($email) && empty($password)){//Both fields are empty.
                    $error->add('empty_username', SWPL_Settings::getString(SWPL_Settings::STRING_LOG_EMPTY_BOTH_FIELDS) );
                    return $error;
                }
            }

            if(empty($email)){ //No email
                $error->add('empty_username', SWPL_Settings::getString(SWPL_Settings::STRING_LOG_EMPTY_EMAIL));
            }
            else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){ //Invalid Email
                $error->add('invalid_username', SWPL_Settings::getString(SWPL_Settings::STRING_LOG_INVALID_EMAIL));
            }

            if(empty($password)){ //No password
                $error->add('empty_password', SWPL_Settings::getString(SWPL_Settings::STRING_LOG_EMPTY_PASSWORD));
            }

            return $error;
        }

        //Check if user exists in WordPress database
        $user = get_user_by('email', $email);

        //bad email
        if(!$user){
            $error = new WP_Error();
            $error->add('invalid', SWPL_Settings::getString(SWPL_Settings::STRING_LOG_INVALID_EMAIL_PASSWORD));
            return $error;
        }
        else{ //check password
            if(!wp_check_password($password, $user->user_pass, $user->ID)){ //bad password
                $error = new WP_Error();
                $error->add('invalid', SWPL_Settings::getString(SWPL_Settings::STRING_LOG_INVALID_EMAIL_PASSWORD));
                return $error;
            }else{
                return $user; //passed
            }
        }
    }
    
    /**
     * Return custom string message
     */
    public function callback_LoginFormLogin(){        
        //Change "Username" text to "Email"
        add_filter('gettext', array($this, 'callback_LoginFormGettext'), 20, 3);
    } 
    
    public function callback_LoginFormGettext($translated_text, $text, $domain ){
        switch($translated_text){
            case 'Username': //For Login
                $translated_text = 'Email';
                break;
            case 'Check your e-mail for the confirmation link.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_RP_LOG_CHECK_EMAIL);
                break;
            case 'Registration complete. Please check your e-mail.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_REG_LOG_REGISTRATION_COMPLETE);
                break;                
        }

        return $translated_text;
    }
    
    

    ############################################################################
    #  Register With Email
    ############################################################################
    
    /**
     * Hook to registration system,
     * Now the tweak goes here.
     * Username: As WP registration requires username, we need to provide a username while
     * registering. So we assign local part of email as username, ex: demo#demo@example.com
     * and username would be demodemo (no special chars).
     * 
     * Duplicate Username: In case username already exists, system tries to change
     * username by adding a random number as suffix. Random number is between
     * 1 to 999. Ex: demodemo_567
     */
    public function callback_LoginFormRegister(){
        $this->setTemporaryUsernameInRequest('user_login', 'user_email');
        
        //Change Registration related text
        add_filter('gettext', array($this, 'callback_RegisterGettext'), 20, 3); 
    }
    
    /**
     * Remove registration message for username
     */
    public function callback_RegistrationErrors($wp_error, $sanitized_user_login, $user_email){
        if(isset($wp_error->errors['empty_username'])){
            unset($wp_error->errors['empty_username']);
        }

        if(isset($wp_error->errors['username_exists'])){
            unset($wp_error->errors['username_exists']);
        }
        return $wp_error;
    }

    /**
     * Ensure necessary _POST variables are present
     */
    public function callback_BPPreSignup() {
        if (!isset($_POST['signup_username'])) {
            $_POST['signup_username'] = null;
        }
    }

    /**
     * Remove registration message for username
     * @param array $result Existing error result:
     *     string user_name
     *     string user_email
     *     WP_Error errors
     */
    public function callback_BPRegistrationErrors($result){
        if (isset($result['errors']->errors) && isset($result['errors']->errors['user_name'])) {
            unset($result['errors']->errors['user_name']);
        }

        return $result;
    }

    /**
     * Inject a temporary username into the signup process
     */
    public function callback_BPSignupValidation() {
        $this->setTemporaryUsernameInRequest('signup_username', 'signup_email');
    }

    /**
     * Change temporary username into permanent. Creates hash from user_id

     * @param int $user_id
     * @param string $user_login
     * @param string $user_password
     * @param string $user_email
     * @param array $usermeta
     */
    public function callback_BPFinaliseSignup($user_id, $user_login, $user_password, $user_email, $usermeta) {
        global $wpdb;
        
        $salt = 'smart-wp-login';
        $hashids = new Hashids\Hashids($salt);
        $new_user_login = $hashids->encode($user_id);
        $signups_table = buddypress()->members->table_name_signups;
        $users_table = $wpdb->prefix.'users';

		$wpdb->update(
            $signups_table,
            array( 'user_login' => $new_user_login ),
            array( 'user_login' => $user_login ),
            array( '%s' ),
            array( '%s' )
        );

        $wpdb->update(
            $users_table,
            array(
                'user_login' => $new_user_login,
                'user_nicename' => $new_user_login,
            ),
            array( 'ID' => $user_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        wp_cache_delete($user_id, 'userslugs');
        wp_cache_delete($user_id, 'userlogins');
        wp_cache_delete($user_id, 'users');            
        wp_cache_delete('bp_core_userdata_' . $user_id, 'bp');
    }

    /**
     * Set the $_POST global values for registration
     * 
     * @param $usernameKey Array key for the username. WP uses user_login, BP signup_username
     * @param $emailKey Array key for the email. WP user user_email, BP signup_email
     */
    private function setTemporaryUsernameInRequest($usernameKey, $emailKey) {
        if(isset($_POST[$emailKey]) && !empty($_POST[$emailKey])){
            //In case user email contains single quote ', WP will add a slash automatically. Yes, emails can use special chars, see RFC 5322
            $_POST[$emailKey] = stripslashes($_POST[$emailKey]);
            
            // Split out the local and domain parts
            list( $local, ) = explode( '@', $_POST[$emailKey], 2 );
        
            //Sanitize special characters in email fields, if any. Yes, emails can use special chars, see RFC 5322
            $_POST[$usernameKey] = sanitize_user($local, true);
            
            $pre_change = $_POST[$usernameKey];
            //In case username already exists, change it
            while(username_exists($_POST[$usernameKey])){
                $_POST[$usernameKey] = $pre_change . '_' . rand(1, 999);
            }
        }
    }
    
    /**
     * Bit tweaking to hide username field
     */
    public function callback_LoginHead(){
        //Don't show username field in register form.
        ?>
            <style>
                #registerform > p:first-child{
                    visibility: hidden;
                    display:none;
                }
            </style>
        <?php
    }
    
    /**
     * Just a backup to remove username field, although css is suffice
     */
    public function callback_LoginFooter(){
        ?>
            <script type="text/javascript">
                try{
                    var swpl_username_p = document.getElementById('registerform').children[0];
                    swpl_username_p.style.display = 'none';
                }catch(e){}
                
                //Focus email
                try{document.getElementById('user_email').focus();}catch(e){}
            </script>
        <?php
        
    }
    
    public function callback_RegisterGettext($translated_text, $text, $domain ){
        switch($translated_text){
            case '<strong>ERROR</strong>: Please type your e-mail address.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_REG_EMPTY_EMAIL);
                break;
            case '<strong>ERROR</strong>: The email address isn&#8217;t correct.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_REG_INVALID_EMAIL);
                break;
            case '<strong>ERROR</strong>: This email is already registered, please choose another one.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_REG_REGISTERED_EMAIL);
                break;
        }

        return $translated_text;
    }
    

    ############################################################################
    #  Reset Password With Email
    ############################################################################
    
    public function callback_LoginFormLostPassword(){
        if('post' == strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST['user_login'])){
            
            //To skip default wordpress processing.
            $_SERVER['REQUEST_METHOD'] = ':(';  
            global $errors;
            
            if(empty($_POST['user_login'])){
                $errors->errors['empty_username'] = array(SWPL_Settings::getString(SWPL_Settings::STRING_RP_EMPTY_EMAIL));
            
                //In case of error, later restore previous REQUEST_METHOD value
                add_action('lost_password', array($this, 'callback_LostPassword'));
            }else if(!filter_var($_POST['user_login'], FILTER_VALIDATE_EMAIL)){
                $errors->errors['invalid_combo'] = array(SWPL_Settings::getString(SWPL_Settings::STRING_RP_INVALID_EMAIL));
                            
                //In case of error, later restore previous REQUEST_METHOD value
                add_action('lost_password', array($this, 'callback_LostPassword'));
            }else{ //Don't skip now
                $_SERVER['REQUEST_METHOD'] = 'POST';
            }
        }

        //Change "Retrieve Password" related text
        add_filter('gettext', array($this, 'callback_LostPasswordGettext'), 20, 3); 
    }    
    
    public function callback_LostPassword(){
        //Restore right value
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }
    
    public function callback_LostPasswordGettext($translated_text, $text, $domain ){
        switch($translated_text){
            case 'Please enter your username or e-mail address. You will receive a new password via e-mail.':
            case 'Please enter your username or email address. You will receive a link to create a new password via email.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_RP_NOTIFICATION);
                //$translated_text = 'Please enter your email address. You will receive a link to create a new password via email.';
                break;
            case '<strong>ERROR</strong>: There is no user registered with that email address.':
                $translated_text = SWPL_Settings::getString(SWPL_Settings::STRING_RP_NO_USER_REGISTERED);
                break;
            case 'Username or E-mail:':
                $translated_text = __('E-mail:', 'smart-wp-login');
                break;
        }

        return $translated_text;
    }
}