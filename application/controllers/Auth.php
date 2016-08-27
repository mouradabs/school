<?php 
/**
* 
*/
class Auth extends CI_Controller
{
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('User');
	}

	public function login()
	{
		
		$this->load->view('auth/login');

	}

	public function do_login()
	{

		$this->load->library('form_validation');
		
		// get form input
		$email = $this->input->post("email");
        $password = $this->input->post("password");

		// form validation
		$this->form_validation->set_rules("email", "Email", "trim|required");
		$this->form_validation->set_rules("password", "Mot de passe", "trim|required");
		
		if ($this->form_validation->run() == FALSE)
        {
			// validation fail
			$this->load->view('auth/login');
		}
		else
		{
			// check for user credentials
			$uresult = $this->User->get_user($email, $password);
			if (count($uresult) > 0)
			{
				// set session
				$sess_data = array('login' => TRUE, 'name' => $uresult[0]->name, 'uid' => $uresult[0]->id);
				$this->session->set_userdata($sess_data);
				$this->session->set_flashdata('msg', 'Logged in');
				redirect("/");

			}
			else
			{
				$this->session->set_flashdata('msg', 'E-mail ou mot de passe invalides');
				redirect('auth/login');
			}
		}
	}


	public function logout()
	{
		


        $this->session->sess_destroy();
        $this->session->set_flashdata('msg', 'Logged out');
		$this->login();


	}
}
