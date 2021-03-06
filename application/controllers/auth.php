<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * This file is part of Auth_AD.

    Auth_AD is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Auth_AD is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Auth_AD.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

/**
 * @package         Auth_AD
 * @subpackage      example
 * @author          Mark Kathmann <mark@stackedbits.com>
 * @version         0.4
 * @link            http://www.stackedbits.com/
 * @license         GNU Lesser General Public License (LGPL)
 * @copyright       Copyright © 2013 Mark Kathmann <mark@stackedbits.com>
 */

class Auth extends CI_Controller 
{
	function __construct() 
	{
		parent::__construct();
		
		// this loads the Auth_AD library. You can also choose to autoload it (see config/autoload.php)
		$this->load->library('Auth_AD');
		$this->load->model('user_model','user');
	}
	
	public function login()
	{
		//check if already logged in
		if($this->auth_ad->is_authenticated())
		{
			redirect('/home');
		}
		// read the form fields, lowercase the username for neatness
		$username = strtolower($this->input->post('username'));
		$password = $this->input->post('password');
		
		$this->load->model('user_model','user');
		// check if user has access to app. (listed in user table)
		$row=$this->user->get_by('uname',$username);
		
		if(count($row)==0 || $row->active==false){
			redirect('/login/loginFail');
		}
		if($row->ltype == 1){
			// try to login
			if($this->auth_ad->login($username, $password))
			{
				$this->session->set_userdata('user_id',$row->userId_pk);
				$this->session->set_userdata('utype',$row->utype);
				redirect('/home');
			}
			else
			{
				// user could not be authenticated, whoops.
				if($this->session->userdata('logged_in'))
				{
					$this->auth_ad->logout();
				}
				$this->session->sess_destroy();
				redirect('/login/loginFail');
			}
		}
		else if($row->ltype==2){
			$enc_pass = $this->encrypt->sha1($password);
			
			if($row->passwd == $enc_pass){
				$this->session->set_userdata('user_id',$row->userId_pk);
				$this->session->set_userdata('utype',$row->utype);
				$this->session->set_userdata('logged_in',TRUE);
				$this->session->set_userdata('username',$row->uname);
				redirect('/home');
			}
			else{
				if($this->session->userdata('logged_in'))
				{
					$this->session->set_userdata('logged_in',FALSE);
				}
				$this->session->sess_destroy();
				redirect('/login/loginFail');
			}
		}
		else{
			redirect('/login/loginFail');
		}
	}
	
	public function logout()
	{
		// perform the logout
		if($this->session->userdata('logged_in'))
		{
			$this->auth_ad->logout();
		}
		redirect('/login/logoutSuccess');
	}
	
	public function useringroup()
	{
		// check if the user is a member of a particular group (recursive search)
		if ($this->auth_ad->in_group($username, $groupname))
		{
			// the user is a member of the group
		}
		else 
		{
			// nope, not a member
		}
	}
}

/* End of file auth.php */
/* Location:  ./application/controllers/auth.php*/