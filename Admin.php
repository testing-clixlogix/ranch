<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller 
{

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
        //Load Login View
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) 
        {
        $data['total_user']=$this->admin_model->users();
        $data['total_cattle']= $this->admin_model->countCattle();
        $data['total_hay']=$this->admin_model->countHay();
        $data['total_openrange']=$this->admin_model->countOpenrange();
        $data['total_orders']= $this->admin_model->countOrder();
        $data['confirm_orders']=$this->admin_model->countCarrierOrders();
        $data['posts']=$this->admin_model->getPosts(4,0);
        $data['orders']=$this->admin_model->getAllOrder(11,0);
        $this->layout->view('admin/welcome',$data);
        } 
        else 
        {
            $this->load->view('login/login');
        }
    }

    public function addUser()
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('fname','First Name','trim|required');
        $this->form_validation->set_rules('lname','Last Name','trim|required');
        $this->form_validation->set_rules('username','Username','trim|required|min_length[4]|max_length[12]|is_unique[users.ga_username]|alpha_dash');
        $this->form_validation->set_rules('m_email','Email','trim|required|valid_email|is_unique[users.ga_email]');
        $this->form_validation->set_rules('m_phone','Contact Number','required');
        $this->form_validation->set_rules('password','Password','trim|required|min_length[6]');
        $this->form_validation->set_rules('passconf','Password Confirmation','trim|required|matches[password]');

        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/add_user');
        }
        else
        {

                $config['upload_path']          = './assets/images/user';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 1024;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;
                $data = $this->input->post();

                $this->load->library('upload', $config);

                 if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/add_user');
                }
                else{
                 $data['image']=base_url('assets/images/user').'/'.$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }

                
                $date = date('Y-m-d H:i:s');
                $register  = array(
                    'fname'      => $data['fname'],
                    'lname'      => $data['lname'],
                    'username'   => $data['username'],
                    'email'      => $data['m_email'],
                    'password'   => md5($data['password']),
                    'role'       => 1,
                    'image_url'      => $data['image'],
                    'created'    => $date
                    );

                $result = $this->admin_model->addUser($register);
                if ($result) 
                {
                    $success = "<strong> User Successfully Added !! </strong>";
                    $this->session->set_flashdata('flashSuccess', $success);
                    redirect('Admin/listUsers');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/listUsers');
                }
            }
           
    }

    public function listUsers()
    {
                $data['page'] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		
		$this->load->library('pagination');
		$config['base_url'] = site_url('/Admin/listUsers');
		$config['per_page'] = 6;
		$config["uri_segment"] = 3;
		$config["total_rows"] = $this->admin_model->users();
		$choice = $config["total_rows"] / $config["per_page"];
		$config["num_links"] = floor($choice);
		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';
		$config['first_link'] = false;
		$config['last_link'] = false;
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';
		$config['prev_link'] = '&laquo';
		$config['prev_tag_open'] = '<li class="prev">';
		$config['prev_tag_close'] = '</li>';
		$config['next_link'] = '&raquo';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="active"><a href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';
		$this->pagination->initialize($config);
		$data['sn']=$data['page'];
		$data['users'] = $this->admin_model->listUsers($config["per_page"],$data['page']);
		$i=0;
        $data['pagination'] = $this->pagination->create_links();
        $this->layout->view('admin/list_users',$data);
    }
    public function edit($id)
    {
        $data['users'] = $this->admin_model->getUsersById($id);
        $this->layout->view('admin/edit_users', $data);
    }
    
    public function updateUser()
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('first_name','First Name','trim|required');
        $this->form_validation->set_rules('email','Email','trim|required|valid_email');
        $this->form_validation->set_rules('role','Role','required');

        $data = $this->input->post();
   
        $id=$data['id'];
        $data1['users'] = $this->admin_model->getUsersById($id);

        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/edit_users',$data1);
        }
        else
        {
            
          
                $config['upload_path']          = '/home/ranchbuilder/public_html/assets/upload/user/';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 10240;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;

                $this->load->library('upload', $config);
                if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                   
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/edit_users',$data1);
                }
                else{
                
                 $data['image']=$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }
                
      
                $register  = array(
                    'first_name'      => $data['first_name'],
                    'last_name'      => $data['last_name'],
                    'email'      => $data['email'],
                    'role'     => $data['role'],
                    'phone'     => $data['phone'],
                    'image_url'  =>$data['image']
                    );

                $register=array_filter($register);

                $result = $this->admin_model->updateUser($register,$id);
                if ($result ==true) 
                {
                    $this->admin_model->updateCarrierDetails($id,$data);
                    $companydetails  = array(
                    'address'       => $data['address'],
                    'house_no'      => $data['house_no'],
                    'street_name'   => $data['street_name'],
                    'city'     => $data['city'],
                    'state'     => $data['state'],
                    'zip_code'      => $data['zip_code'],
                    'country'   => $data['country'],
                    'company_name'  => $data['company_name'],
                    'about'     => $data['about']
                    );
                    if(!empty($data['cd_id'])){
                    $cresult = $this->admin_model->updateCompanyDetails($companydetails,$data['cd_id']);
                    }else{
                     $companydetails['user_id']=$id;
                     $cresult = $this->admin_model->insertCompanyDetails($companydetails);
                    }
                    $success = "<strong> User Successfully Updated !! </strong>";
                    $this->session->set_flashdata('flashSuccess', $success);
                    redirect('Admin/listUsers');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/listUsers');
                }
            }
           
      
    }
    public function massDelete() {

        $checkboxes = $this->input->post('checkbox');
        if (is_array($checkboxes) && !empty($checkboxes)) {
            $result = $this->admin_model->deleteMassUsers($checkboxes);
            $success = "<strong>success!</strong>  " . $result . " row has been deleted  !!!";
            $this->session->set_flashdata('flashSuccess', $success);
            redirect('Admin/listUsers');
        } else {
            $errors = "<strong>Eroor!</strong> !!!";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/listUsers');
        }
    }
    
    public function userDelete($id) {

     
        if (!empty($id)) {
            $result = $this->admin_model->userDelete($id);
            $success = "<strong>success!</strong>  User has been deleted  !!!";
            $this->session->set_flashdata('flashSuccess', $success);
            redirect('Admin/listUsers');
        } else {
            $errors = "<strong>Eroor!</strong> !!!";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/listUsers');
        }
    }

    public function upload()
    {
        $attachment = $this->input->post('attachment');
        $uploadedFile = $_FILES['attachment']['tmp_name']['file'];

        $path = $_SERVER["DOCUMENT_ROOT"].'/admin/assets/images';
        $url = base_url().'assets/images';

        // create an image name
        $fileName = $attachment['name'];

        // upload the image
        move_uploaded_file($uploadedFile, $path.'/'.$fileName);

        $this->output->set_output(json_encode(array('file' => array(
        'url' => $url . '/' . $fileName,
        'filename' => $fileName
        ))),
        200,
        array('Content-Type' => 'application/json')
        );
    }
    
    // Banner Module Strat form here

    public function banner()
    {
        $data['page'] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->load->library('pagination');
        $config['base_url'] = site_url('/Admin/listUsers');
        $config['per_page'] = 10;
        $config["uri_segment"] = 3;
        $config["total_rows"] = $this->admin_model->bannerCount();
        $choice = $config["total_rows"] / $config["per_page"];
        $config["num_links"] = floor($choice);
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);
        $data['sn']=$data['page'];
        $data['users'] = $this->admin_model->listBanner($config["per_page"],$data['page']);
        $i=0;
                $data['pagination'] = $this->pagination->create_links();
                $this->layout->view('admin/banners', $data);
    }
    
    public function addBanner()
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('description','Description','trim|required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/addBanner');
        }
        else
        {
                $data = $this->input->post();

                $config['upload_path']          = './assets/images/banners';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 10240;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;

                $this->load->library('upload', $config);
                if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                   
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/addBanner');
                }
                else{
                
                 $data['image']=base_url('assets/images/banners').'/'.$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }
                
      
                $register  = array(
                    'description'      => $data['description'],
                    'slideLink'      => $data['slideLink'],
                    'slideAlt'     => $data['slideAlt'],
                    'slideOrder'     => $data['slideOrder'],
                    'slideImage'  =>$data['image'],
                    'pageID'      =>$data['pageID']
                    );

                $register=array_filter($register);

                $result = $this->admin_model->insertBanner($register);
                if($result)
                {
                    $success = "<strong> Banner Successfully Added !! </strong>";
                    $this->session->set_flashdata('flashSuccess',$success);
                    redirect('Admin/banner');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/banner');
                }
            }
           
    }
    
    public function editBanner($id)
    {
        $data['users'] = $this->admin_model->getBannerById($id);
        $this->layout->view('admin/editBanner', $data);
    }

    public function updateBanner($id)
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('description','Description','trim|required');

        $data = $this->input->post();
        $data1['users'] = $this->admin_model->getBannerById($id);

        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/editBanner',$data1);
        }
        else
        {
            
          
                $config['upload_path']          = './assets/images/banners';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 10240;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;

                $this->load->library('upload', $config);
                if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                   
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/editBanner',$data1);
                }
                else{
                
                 $data['image']=base_url('assets/images/banners').'/'.$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }
                
      
                $register  = array(
                    'description'      => $data['description'],
                    'slideLink'      => $data['slideLink'],
                    'slideAlt'     => $data['slideAlt'],
                    'slideOrder'     => $data['slideOrder'],
                    'slideImage'  =>$data['image']
                    );

                $register=array_filter($register);

                $result = $this->admin_model->updateBanner($register,$id);
                if($result ==true)
                {
                    $success = "<strong> Banner Successfully Updated !! </strong>";
                    $this->session->set_flashdata('flashSuccess',$success);
                    redirect('Admin/banner');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/banner');
                }
            }
           
      
    }
    public function deleteBanner($id) {

        $checkboxes = $this->input->post('checkbox');
        if (!empty($id)) {
            $result = $this->admin_model->deleteBanner($id);
            $success = "<strong>success!</strong> Banner Deleted Successfully   !!!";
            $this->session->set_flashdata('flashSuccess', $success);
            redirect('Admin/banner');
        } else {
            $errors = "<strong>Eroor!</strong> !!!";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/banner');
        }
    }



    //Banner Module end here 

    // Testimonial Module Strat form here

    public function testimonials()
    {
        $data['page'] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->load->library('pagination');
        $config['base_url'] = site_url('/Admin/listUsers');
        $config['per_page'] = 10;
        $config["uri_segment"] = 3;
        $config["total_rows"] = $this->admin_model->testimonialsCount();
        $choice = $config["total_rows"] / $config["per_page"];
        $config["num_links"] = floor($choice);
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);
        $data['sn']=$data['page'];
        $data['users'] = $this->admin_model->listTestimonials($config["per_page"],$data['page']);
        $i=0;
                $data['pagination'] = $this->pagination->create_links();
                $this->layout->view('admin/testimonials', $data);
    }
    
    public function addTestimonial()
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('name','name','trim|required');

        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/addTestimonial');
        }
        else
        {
                $data = $this->input->post();

                $config['upload_path']          = './assets/images';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 10240;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;

                $this->load->library('upload', $config);
                if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                   
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/editTestimonial',$data1);
                }
                else{
                
                 $data['image']=base_url('assets/images').'/'.$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }
                
      
                $register  = array(
                    'name'      => $data['name'],
                    'designation'=> $data['designation'],
                    'message'     => $data['message'],
                    'imageurl'  =>$data['image']
                    );

                $register=array_filter($register);
                $result = $this->admin_model->insertTestimonial($register);
                if($result ==true)
                {
                    $success = "<strong> Testimonial Successfully Added !! </strong>";
                    $this->session->set_flashdata('flashSuccess',$success);
                    redirect('Admin/testimonials');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/testimonials');
                }
            }
           
    }


    public function editTestimonial($id)
    {
        $data['users'] = $this->admin_model->getTestimonialById($id);
        $this->layout->view('admin/editTestimonial',$data);
    }

    public function updateTestimonial($id)
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('name','Name','trim|required');
        $data = $this->input->post();
        $data1['users'] = $this->admin_model->getTestimonialById($id);
        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/editTestimonial',$data1);
        }
        else
        {

                $config['upload_path']          = './assets/images';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 10240;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;

                $this->load->library('upload', $config);
                if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                   
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/editTestimonial',$data1);
                }
                else{
                
                 $data['image']=base_url('assets/images').'/'.$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }
                
      
                $register  = array(
                    'name'      => $data['name'],
                    'designation'=> $data['designation'],
                    'message'     => $data['message'],
                    'imageurl'  =>$data['image']
                    );

                $register=array_filter($register);
                $result = $this->admin_model->updateTestimonial($register,$id);
                if($result ==true)
                {
                    $success = "<strong> Testimonial Successfully Updated !! </strong>";
                    $this->session->set_flashdata('flashSuccess',$success);
                    redirect('Admin/testimonials');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/testimonials');
                }
            }
           
    }

    public function deleteTestimonial($id) {

        $checkboxes = $this->input->post('checkbox');
        if (!empty($id)) {
            $result = $this->admin_model->deleteTestimonial($id);
            $success = "<strong>success!</strong> Testimonial Deleted Successfully   !!!";
            $this->session->set_flashdata('flashSuccess', $success);
            redirect('Admin/testimonials');
        } else {
            $errors = "<strong>Eroor!</strong> !!!";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/testimonials');
        }
    }

    //testimonials Module end here 

    

    // Testimonial Module Strat form here

    public function markets()
    {
        $data['page'] = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->load->library('pagination');
        $config['base_url'] = site_url('/Admin/listUsers');
        $config['per_page'] = 10;
        $config["uri_segment"] = 3;
        $config["total_rows"] = $this->admin_model->marketsCount();
        $choice = $config["total_rows"] / $config["per_page"];
        $config["num_links"] = floor($choice);
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);
        $data['sn']=$data['page'];
        $data['users'] = $this->admin_model->listMarkets($config["per_page"],$data['page']);
        $i=0;
                $data['pagination'] = $this->pagination->create_links();
                $this->layout->view('admin/markets', $data);
    }
    public function editMarkets($id)
    {
        $data['users'] = $this->admin_model->getMarketsById($id);
        $this->layout->view('admin/editMarkets',$data);
    }

    public function updateMarkets($id)
    {
        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('heading','heading','trim|required');
        $data = $this->input->post();
        $data1['users'] = $this->admin_model->getMarketsById($id);
        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/editMarkets',$data1);
        }
        else
        {

                $config['upload_path']          = './assets/images';
                $config['allowed_types']        = 'gif|jpg|png|jpeg';
                $config['max_size']             = 10240;
                $config['max_filename']         = 150;
                $config['remove_spaces']        = TRUE;

                $this->load->library('upload',$config);
                if(!empty($_FILES['image']['name'])){
                if(!$this->upload->do_upload('image'))
                {
                    $error = $this->upload->display_errors('<p>', '</p>');
                    $this->session->set_flashdata('flashError', $error);
                    $this->layout->view('admin/editMarkets',$data1);
                }
                else{
                
                 $data['image']=base_url('assets/images').'/'.$this->upload->data('file_name');
              
                }
                }else{
                 $data['image']='';
                }
                
      
                $register  = array(
                    'heading'      => $data['heading'],
                    'description'=> $data['description'],
                    'link'     => $data['link'],
                    'image'  =>$data['image']
                    );

                $register=array_filter($register);
                $result = $this->admin_model->updateMarkets($register,$id);
                if($result ==true)
                {
                    $success = "<strong> Record Successfully Updated !! </strong>";
                    $this->session->set_flashdata('flashSuccess',$success);
                    redirect('Admin/markets');
                }
                else
                {
                    $error = "<strong> Something went wrong !! </strong>";
                    $this->session->set_flashdata('flashError', $error);
                    redirect('Admin/markets');
                }
            }
           
    }

    public function deleteMarkets($id) {

        $checkboxes = $this->input->post('checkbox');
        if (!empty($id)) {
            $result = $this->admin_model->deleteTestimonial($id);
            $success = "<strong>success!</strong> Record deleted Successfully !!!";
            $this->session->set_flashdata('flashSuccess', $success);
            redirect('Admin/markets');
        } else {
            $errors = "<strong>Eroor!</strong> !!!";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/markets');
        }
    }

    //testimonials Module end here 

    public function social()
    {
        


        $this->data['social'] = $this->admin_model->getSocial();
        $this->data['current'] = $this->uri->segment(2);
        // $this->data['header'] = $this->load->view('admin/header', $this->data, true);
        // $this->data['footer'] = $this->load->view('admin/footer', '', true);
        $this->layout->view('admin/social', $this->data);
    }

    public function updateSocial()
    {
        $this->admin_model->updateSocial();
        $success = "<strong>success!</strong> Data Updated Successfully  !!!";
        $this->session->set_flashdata('flashSuccess', $success);
        redirect('Admin/social');
    }

    public function settings()
    {
        $this->load->helper('directory');
        $this->data['themesdir'] = directory_map($_SERVER["DOCUMENT_ROOT"].'/admin/uploads/', 1);

        $this->data['langdir'] = directory_map(APPPATH.'/language/', 1);

        $this->data['settings'] = $this->admin_model->getSettings();
        $this->data['current'] = $this->uri->segment(2);
        $this->layout->view('admin/settings', $this->data);
    }

    public function updateSettings()
    {
        $path_upload = $_SERVER["DOCUMENT_ROOT"] . '/admin/uploads/';
        $path_images = $_SERVER["DOCUMENT_ROOT"] . '/admin/images/';
       
        if ($this->input->post('siteLogo') != ""){
            rename($path_upload . $this->input->post('siteLogo'), $path_images . $this->input->post('siteLogo'));
        }
        if ($this->input->post('siteFavicon') != ""){
            rename($path_upload . $this->input->post('siteFavicon'), $path_images . $this->input->post('siteFavicon'));
        }
        $this->admin_model->updateSettings();
        redirect('admin/settings');
    }

    public function uploadLogo()
    {
        $config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'gif|jpg|png';
        $this->load->library('upload', $config);
        foreach ($_FILES as $key => $value) {
            if ( ! $this->upload->do_upload($key))
            {
                    $error = array('error' => $this->upload->display_errors());
                    echo 0;
            }
            else
            {
                    echo '"'.$this->upload->data('file_name').'"';
            }
        }
    }

    public function checkSession()
    {
        if(!$this->session->userdata('logged_in')){
            echo 0;
        } else {
            echo 1;
        }
    }
    
    // chages password
    
     public function changePassword()
    {
        
        
         $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('oldpassword','oldpassword','trim|required');
        $this->form_validation->set_rules('newpassword','newpassword','trim|required');
        
        $this->form_validation->set_rules('confirmpassword','confirmpassword','trim|required');
        
     
        if ($this->form_validation->run() == FALSE)
        {
            $this->layout->view('admin/changePassword');
        }
        else
        {
            $data = $this->input->post();
            
            $status= $this->admin_model->matchOldpassword($data['oldpassword']);
            if($status){
                if($data['newpassword']==$data['confirmpassword']){
                    $password=md5($data['newpassword']);
                    $this->admin_model->updatepassword($password);
                    
                    $success = "<strong>Success!</strong> Password changed Successfully !!!";
                     $this->session->set_flashdata('flashSuccess', $success);
                     
                     redirect('Admin/changePassword');
                }
                else{
                    
            $errors = "<strong>Confirm password not matched with password!</strong>";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/changePassword');
                    
                }
            }else{
                
            $errors = "<strong>Old password not matched!</strong>";
            ;
            $this->session->set_flashdata('flashError', $errors);
            redirect('Admin/changePassword');
                
            }
            
            
            
            
        }
       
    }

}
?>