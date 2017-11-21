<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories extends CI_Controller {

	public function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('form');        
        $this->load->model('admin_model');
        define ('LANG', $this->admin_model->getLang());
        $this->lang->load('admin', LANG);
        if (empty($_SESSION['userInfo'][0]->id)) {
            redirect('login');
        }
    }

	public function index()
	{

	$this->load->library('pagination');
    $result_per_page =15;  // the number of result per page
    $config['base_url'] = site_url().'/posts/categories/';
    $config['total_rows'] = $this->admin_model->countCategories();
	$config['uri_segment'] = 3;
    $config['per_page'] = $result_per_page;
		
    $this->pagination->initialize($config);
		//Get categorys from database
		$this->data['categories'] = $this->admin_model->getCategoriesAll($result_per_page, $this->uri->segment(3));
		//Load the view
		
		$this->layout->view('admin/post_categories',$this->data);
	}

	public function addCategory()
	{
		//Load the view
		$this->layout->view('admin/post_category_new');
	}

	public function confirm()
	{
		//Load the form validation library
		$this->load->library('form_validation');
		//Set validation rules
		$this->form_validation->set_rules('categorySlug', 'category slug', 'trim|alpha_dash|required|is_unique[post_category.categorySlug]');
		$this->form_validation->set_rules('categoryTitle', 'category title', 'trim|required');

		if($this->form_validation->run() == FALSE) {
			//Validation failed
			$this->addCategory();
		}  else  {
			//Validation passed
			//Add the category
			$this->admin_model->createCategory();
			//Return to category list
			$success = "<strong> Category Added Successfully !! </strong>";
            $this->session->set_flashdata('flashSuccess', $success);
			redirect('/categories', 'refresh');
	  	}
	}

	public function editCategory()
	{
		//Get category details from database
		$this->data['category'] = $this->admin_model->getCategory($this->uri->segment(3));
		//Load the view
		$this->layout->view('admin/post_category_edit',$this->data);
	}

	public function edited()
	{
		//Load the form validation library
		$this->load->library('form_validation');
		//Set validation rules
		$this->form_validation->set_rules('categorySlug', 'category slug', 'trim|alpha_dash|required');
		$this->form_validation->set_rules('categoryTitle', 'category title', 'trim|required');

		if($this->form_validation->run() == FALSE) {
			//Validation failed
			$this->editCategory();
		}  else  {
			//Validation passed
			//Update the category
			$result=$this->admin_model->updateCategory($this->uri->segment(3));
			//Return to category list
                        if ($result == true) 
                        {
                            $success = "<strong> Data Updated Successfully !! </strong>";
                            $this->session->set_flashdata('flashSuccess', $success);
                            redirect('Categories/editCategory/'.$this->uri->segment(3));
                        }
                        else
                        {
                           $error = "<strong> Something went wrong !! </strong>";
                           $this->session->set_flashdata('flashError', $error);
                           redirect('Categories/editCategory/'.$this->uri->segment(3));
                        }
	  	}
	}

	function delete()
	{
		if($this->input->post('deleteid')):
			$this->admin_model->removeCategory($this->input->post('deleteid'));
		    $success = "<strong> Category Deleted Successfully !! </strong>";
            $this->session->set_flashdata('flashSuccess', $success);
			redirect('/categories');
		else:
			$this->data['form']=$this->admin_model->getCategory($this->uri->segment(3));
			$this->load->view('admin/post_category_delete.php', $this->data );
		endif;
	}

}