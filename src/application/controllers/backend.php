<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Backend extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
    }
    
    /**
     * Display the main backend page.
     * 
     * This method displays the main backend page. All users login permission can 
     * view this page which displays a calendar with the events of the selected 
     * provider or service. If a user has more priviledges he will see more menus  
     * at the top of the page.
     * 
     * @param string $appointment_hash If given, the appointment edit dialog will 
     * appear when the page loads.
     */
    public function index($appointment_hash = '') {
        $this->session->set_userdata('dest_url', $this->config->item('base_url') . 'backend');
        if (!$this->hasPrivileges(PAGE_APPOINTMENTS)) return;
        
        $this->load->model('appointments_model');
        $this->load->model('providers_model');
        $this->load->model('services_model');
        $this->load->model('customers_model');
        $this->load->model('settings_model');
        
        $view['base_url'] = $this->config->item('base_url');
        $view['book_advance_timeout'] = $this->settings_model->get_setting('book_advance_timeout');
        $view['company_name'] = $this->settings_model->get_setting('company_name');
        $view['available_providers'] = $this->providers_model->get_available_providers();
        $view['available_services'] = $this->services_model->get_available_services();
        
        if ($appointment_hash != '') {
            $results = $this->appointments_model->get_batch(array('hash' => $appointment_hash));
            $appointment = $results[0];
            $appointment['customer'] = $this->customers_model->get_row($appointment['id_users_customer']);
            $view['edit_appointment'] = $appointment; // This will display the appointment edit dialog on page load.
        } else {
            $view['edit_appointment'] = NULL;
        }
        
        $this->load->view('backend/header', $view);
        $this->load->view('backend/calendar', $view);
        $this->load->view('backend/footer', $view);
    }
    
    /**
     * Display the backend customers page.
     * 
     * In this page the user can manage all the customer records of the system.
     */
    public function customers() {
        $this->session->set_userdata('dest_url', $this->config->item('base_url') . 'backend/customers');
    	if (!$this->hasPrivileges(PAGE_CUSTOMERS)) return;
    	
        $this->load->model('providers_model');
        $this->load->model('customers_model');
        $this->load->model('services_model');
        $this->load->model('settings_model');
        
        $view['base_url'] = $this->config->item('base_url');
        $view['company_name'] = $this->settings_model->get_setting('company_name');
        $view['customers'] = $this->customers_model->get_batch();
        $view['available_providers'] = $this->providers_model->get_available_providers();
        $view['available_services'] = $this->services_model->get_available_services();
        
        $this->load->view('backend/header', $view);
        $this->load->view('backend/customers', $view);
        $this->load->view('backend/footer', $view);
    }
    
    /**
     * Displays the backend services page. 
     * 
     * Here the admin user will be able to organize and create the services 
     * that the user will be able to book appointments in frontend. 
     * 
     * NOTICE: The services that each provider is able to service is managed 
     * from the backend services page. 
     */
    public function services() {
        $this->session->set_userdata('dest_url', $this->config->item('base_url') . 'backend/services');
        if (!$this->hasPrivileges(PAGE_SERVICES)) return;
        
        $this->load->model('customers_model');
        $this->load->model('services_model');
        $this->load->model('settings_model');
        
        $view['base_url'] = $this->config->item('base_url');
        $view['company_name'] = $this->settings_model->get_setting('company_name');
        $view['services'] = $this->services_model->get_batch();
        $view['categories'] = $this->services_model->get_all_categories();
        
        $this->load->view('backend/header', $view);
        $this->load->view('backend/services', $view);
        $this->load->view('backend/footer', $view);
    }
    
    /**
     * Display the backend users page.
     * 
     * In this page the admin user will be able to manage the system users. 
     * By this, we mean the provider, secretary and admin users. This is also
     * the page where the admin defines which service can each provider provide.
     */
    public function users() {
        $this->session->set_userdata('dest_url', $this->config->item('base_url') . 'backend/users');
        if (!$this->hasPrivileges(PAGE_USERS)) return;
        
        $this->load->model('providers_model');
        $this->load->model('secretaries_model');
        $this->load->model('admins_model');
        $this->load->model('services_model');
        $this->load->model('settings_model');
        
        $view['base_url'] = $this->config->item('base_url');
        $view['company_name'] = $this->settings_model->get_setting('company_name');
        $view['admins'] = $this->admins_model->get_batch();
        $view['providers'] = $this->providers_model->get_batch();
        $view['secretaries'] = $this->secretaries_model->get_batch();
        $view['services'] = $this->services_model->get_batch(); 
        
        $this->load->view('backend/header', $view);
        $this->load->view('backend/users', $view);
        $this->load->view('backend/footer', $view);
    }
    
    /**
     * Display the user/system settings.
     * 
     * This page will display the user settings (name, password etc). If current user is
     * an administrator, then he will be able to make change to the current Easy!Appointment 
     * installation (core settings like company name, book timeout etc). 
     */
    public function settings() {
        $this->session->set_userdata('dest_url', $this->config->item('base_url') . 'backend/settings');
        if (!$this->hasPrivileges(PAGE_SYSTEM_SETTINGS)
                && !$this->hasPrivileges(PAGE_USER_SETTINGS)) return;
        
        $this->load->model('settings_model');
        $this->load->model('user_model');
        
        $this->load->library('session');
        
        // @task Apply data for testing this page (this must be done during the login process).
        $this->session->set_userdata('user_id', 18);
        $this->session->set_userdata('user_slug', DB_SLUG_ADMIN);
        
        $user_id = $this->session->userdata('user_id'); 
        
        $view['base_url'] = $this->config->item('base_url');
        $view['company_name'] = $this->settings_model->get_setting('company_name');
        $view['user_slug'] = $this->session->userdata('user_slug');
        $view['system_settings'] = $this->settings_model->get_settings();
        $view['user_settings'] = $this->user_model->get_settings($user_id);
        
        $this->load->view('backend/header', $view);
        $this->load->view('backend/settings', $view);
        $this->load->view('backend/footer', $view);
    }
    
    /**
     * Check whether current user is logged in and has the required privileges to 
     * view a page. 
     * 
     * The backend page requires different privileges from the users to display pages. Not all
     * pages are avaiable to all users. For example secretaries should not be able to edit the
     * system users.
     * 
     * @see Constant Definition In application/config/constants.php
     * 
     * @param string $page This argument must match the roles field names of each section 
     * (eg "appointments", "users" ...).
     * @param bool $redirect (OPTIONAL - TRUE) If the user has not the required privileges
     * (either not logged in or insufficient role privileges) then the user will be redirected  
     * to another page. Set this argument to FALSE when using ajax.
     * @return bool Returns whether the user has the required privileges to view the page or
     * not. If the user is not logged in then he will be prompted to log in. If he hasn't the
     * required privileges then an info message will be displayed.
     */
    private function hasPrivileges($page, $redirect = TRUE) {       
        // Check if user is logged in.
        $user_id = $this->session->userdata('user_id');
        if ($user_id == FALSE) { // User not logged in, display the login view.
            if ($redirect) {
                header('Location: ' . $this->config->item('base_url') . 'user/login');
            }
            return FALSE;
        }
        
        // Check if the user has the required privileges for viewing the selected page.
        $role_slug = $this->session->userdata('role_slug');
        $role_priv = $this->db->get_where('ea_roles', array('slug' => $role_slug))->row_array();
        if ($role_priv[$page] < PRIV_VIEW) { // User does not have the permission to view the page.
             if ($redirect) {
                header('Location: ' . $this->config->item('base_url') . 'user/no_privileges');
            }
            return FALSE;
        }
        
        return TRUE;
    }
}

/* End of file backend.php */
/* Location: ./application/controllers/backend.php */