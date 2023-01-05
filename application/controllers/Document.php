<?php
class Document extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('m_documents');
		$this->load->helper('url_helper');
	}
	public function index()
	{
		// if user not loggin redirect to login page
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		// det documents data
		$data['documents'] = $this->m_documents->get_documents();
		$this->load->view('templates/header');
		$this->load->view('templates/nav');
		$this->load->view('index', $data);
		$this->load->view('templates/footer');
		// $this->load->view('index');
		// echo 'Hello World!';
	}
	private function send_mail($data)
	{
		$config = array(
			'protocol'  => 'smtp',
			'smtp_host' => 'mail.incoe.astra.co.id',
			'smtp_port' => 25,
			'smtp_user' => 'no-reply@incoe.astra.co.id',
			'smtp_pass' => 'just4unme',
			'mailtype'  => 'html',
			'charset'   => 'iso-8859-1'
		);
		$url = base_url();

		$this->load->library('email', $config);
		$this->email->set_newline("\r\n");
		$name = '';
		if (isset($data['name'])) {
			$name = $data['name'];
		}
		$this->email->from('no-reply@incoe.astra.co.id', $name);
		$this->email->to($data['to']);
		$this->email->subject($data['subject']);
		$this->email->message($data['message']);
		return $this->email->send();
	}
	public function exports()
	{
		// if user not loggin redirect to login page
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		// det documents data



		$listOfDocument = $this->m_documents->get_data_for_exports();
		//  format all Expired Date in listOfDocument from yyyy-mm-dd to yyyy/mm/dd
		foreach ($listOfDocument as $key => $value) {
			$listOfDocument[$key]['Expired Date'] = str_replace('-', '/', $listOfDocument[$key]['Expired Date']);
		}

		//echoing import javascript XLSX library 
		// echo "<script src='assets/js/xlsx.full.min.js'></script>";
		// //then using xlsx utils to convert json to excel
		// echo "<script>var data = " . json_encode($data['documents']) . ";"
		// 	. "var ws = XLSX.utils.json_to_sheet(data);"
		// 	. "var wb = XLSX.utils.book_new();"
		// 	. "XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');"
		// 	. "XLSX.writeFile(wb, 'data.xlsx');</script>";
		// returns $data['documents'] as json
		echo json_encode($listOfDocument);
	}
	public function imports()
	{
		// if user not loggin redirect to login page
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		// check if it's a post request
		// if this is a post request then get json data from ajax post
		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			//get raw json data from ajax post
			$json = file_get_contents('php://input');
			//decode json data to array
			// $data = json_decode($json, true);

			// get json data from ajax post
			// $data = $this->input->post('data');
			// var_dump($data);
			// decode json data
			// $batchData = json_decode($data);
			// var_dump($batchData);
			// insert data to database
			$data = json_decode($json, true);
			$batchData = [];
			//load data to batch data

			for ($i = 0; $i < count($data); $i++) {
				$batchData[$i] = [
					'nama_alat' => isset($data[$i]['nama_alat']) ? $data[$i]['nama_alat'] : '',
					'pabrik_pembuat' =>  isset($data[$i]['pabrik_pembuat']) ? $data[$i]['pabrik_pembuat'] : '',
					'kapasitas' => isset($data[$i]['kapasitas']) ? $data[$i]['kapasitas'] : '',
					'lokasi' => isset($data[$i]['lokasi']) ? $data[$i]['lokasi'] : '',
					'no_seri' => isset($data[$i]['no_seri']) ? $data[$i]['no_seri'] : '',
					'no_perijinan' => isset($data[$i]['no_perijinan']) ? $data[$i]['no_perijinan'] : '',
					'expired_date' => isset($data[$i]['expired_date']) ? $data[$i]['expired_date'] : '',
					'status' => isset($data[$i]['status']) ? $data[$i]['status'] : '',
				];
			}
			// foreach ($data as $key => $value) {
			// 	$batchData[] = [
			// 		'nama_alat' => isset($value['nama_alat']) ? $value['nama_alat'] : "",
			// 		'pabrik_pembuat' => isset($value['pabrik_pembuat']) ? $value['pabrik_pembuat'] : "",
			// 		'kapasitas' => isset($value['kapasitas']) ? $value['kapasitas'] : "",
			// 		'lokasi' => isset($value['lokasi']) ? $value['lokasi'] : "",
			// 		'no_seri' => isset($value['no_seri']) ? $value['no_seri'] : "",
			// 		'no_perijinan' => isset($value['no_perijinan']) ? $value['no_perijinan'] : "",
			// 		'expired_date' => isset($value['expired_date']) ? $value['expired_date'] : "",
			// 	];
			// }
			$this->m_documents->add_multiple_documents($batchData);
			// return success message
			echo 'success';
			// var_dump($data);
			var_dump($batchData);
		} else {
			$this->load->view('templates/header');
			$this->load->view('templates/nav');
			$this->load->view('imports',);
			$this->load->view('templates/footer');
		}
	}
	public function flip_status($id)
	{
		// if user not loggin redirect to login page
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		$this->m_documents->flip_status($id);
		echo "<script>window.location.href='" . base_url() . "';</script>";
	}
	public function add()
	{
		// if user not loggin redirect to login page
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		$data["is_add_documents"] = 'true';
		$this->load->view('templates/header');
		$this->load->view('templates/nav');
		$this->load->view('add',);
		$this->load->view('templates/footer', $data);
	}
	public function upload($id)
	{
		// if user not loggin redirect to login page
		if (!$this->session->userdata('logged_in')) {
			redirect('login');
		}
		// upload file then set file name to database using  setFilenameBy function on model
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'gif|jpg|png|pdf|doc|docx';
		$config['max_size'] = 10000;
		$config['max_width'] = 10240;
		$config['max_height'] = 7680;
		$this->load->library('upload', $config);
		// $this->upload->initialize($config);
		if (!$this->upload->do_upload('file')) {
			$error = array('error' => $this->upload->display_errors());
			//reload to root page
			echo $error['error'];
			echo "<script>alert('Upload gagal!');window.location.href='" . base_url() . "';</script>";
		} else {
			$data = array('upload_data' => $this->upload->data());
			// set file name to database
			$this->m_documents->setFilenameBy($id, $data['upload_data']['file_name']);
			//reload to root page
			echo "<script>alert('Upload berhasil!');window.location.href='" . base_url() . "';</script>";
		}
	}
	public function produceExpiredDocumentSample()
	{
		return	$this->m_documents->produceExpiredDocumentSample();
	}

	public function sendExpiredEmailNotification($idDocument)
	{
		// get data from database
		$item = $this->m_documents->getDocumentById($idDocument);
		$data = array();
		$data['to'] = 'shakaaji29@gmail.com';
		$data['subject'] = 'Document has Expired';
		$data['name'] = 'Document has Expired';
		$data['message'] = 'Dear User,';
		$data['message'] .= '<br>';
		$data['message'] .= 'Document with name ' . $item['nama_alat'] . ' has expired';
		$data['message'] .= '<br>';
		$data['message'] .= 'Please check your document';
		$data['message'] .= '<br>';
		$data['message'] .= 'Thank you';
		$data['message'] .= '<br>';
		$data['message'] .= 'Regards';
		$data['message'] .= '<br>';
		$data['message'] .= 'Admin';


		// send email
		if ($this->send_mail($data)) {
			// show dialog success and redirect to root page
			echo "<script>alert('Email berhasil dikirim!');window.location.href='" . base_url() . "';</script>";
		};
	}

	public function webhook_reminder()
	{
		// get data for reminder from database
		$data = $this->m_documents->getDocumentsForReminders();
		// var_dump($data);
		// set count of reminders data
		$count = count($data);
		// if count is less than 1, return false

		if ($count < 1) {
			echo json_encode(
				[
					"status" => "failed",
					"message" => "no data found"
				]
			);
			return;
		}
		$url = base_url();
		$to = 'shakaaji29@gmail.com';
		$subject = $count . ' Document will Expired';
		$message = "Dear User,";
		$message .= "<br>";
		$message .= "We want to inform you that there are " . $count . " documents will expired.";
		$message .= "<br><br>";
		$message .= "Please check the list below : ";
		$message .= "<br><br>";
		$message .= "<table border='1' style='border-collapse: collapse;'>";
		$message .= "<tr>";
		$message .= "<th>No</th>";
		$message .= "<th>Document Name</th>";
		$message .= "<th>Expired Date</th>";
		$message .= "<th>Link</th>";
		$message .= "</tr>";
		$no = 1;
		foreach ($data as $key => $value) {
			$message .= "<tr>";
			$message .= "<td>" . $no . "</td>";
			$message .= "<td>" . $value['nama_alat'] . "</td>";
			$message .= "<td>" . $value['expired_date'] . "</td>";
			$message .= "<td>" . $url . "uploads/" . $value['filename'] . "</td>";
			$message .= "</tr>";
			$no++;
		}
		$message .= "</table>";
		$message .= "<br><br>";
		$message .= "or you can check full document list in this link : <a href='" . $url . "'>Document List</a>";
		$message .= "<br><br>";
		$message .= "Thanks";
		$message .= "<br>Admin";

		$name = 'Document Expired Reminder';
		$data = array(
			"to" => $to,
			"subject" => $subject,
			"message" => $message,
			"name" => $name
		);
		$this->send_mail($data);

		// $config = array(
		// 	'protocol'  => 'smtp',
		// 	'smtp_host' => 'mail.incoe.astra.co.id',
		// 	'smtp_port' => 25,
		// 	'smtp_user' => 'no-reply@incoe.astra.co.id',
		// 	'smtp_pass' => 'just4unme',
		// 	'mailtype'  => 'html',
		// 	'charset'   => 'iso-8859-1'
		// );

		// $this->load->library('email', $config);
		// $this->email->set_newline("\r\n");
		// $this->email->from('no-reply@incoe.astra.co.id', $name);
		// $this->email->to($to);

		// $this->email->to('nitawulandari215@gmail.com','lukymulana@gmail.com');

		// $this->email->subject($subject);
		// $this->email->message($message);
		// $this->email->send();
	}
	// this function is for send email to user when document is expired
	// [data] is array of data that will be sent to user
	// [data] must have [subject],[message] and [to] key
	// [data] can have [name] key





	// public function view($slug = NULL) {
	// 	$data['document_item'] = $this->Document_model->get_documents($slug);
	// 	if (empty($data['document_item'])) {
	// 		show_404();
	// 	}
	// 	$data['title'] = $data['document_item']['title'];
	// 	$this->load->view('templates/header', $data);
	// 	$this->load->view('document/view', $data);
	// 	$this->load->view('templates/footer');
	// }
	// public function create() {
	// 	$this->load->helper('form');
	// 	$this->load->library('form_validation');
	// 	$data['title'] = 'Create a document item';
	// 	$this->form_validation->set_rules('title', 'Title', 'required');
	// 	$this->form_validation->set_rules('text', 'Text', 'required');
	// 	if ($this->form_validation->run() === FALSE) {
	// 		$this->load->view('templates/header', $data);
	// 		$this->load->view('document/create');
	// 		$this->load->view('templates/footer');
	// 	} else {
	// 		$this->Document_model->set_documents();
	// 		$this->load->view('document/success');
	// 	}
	// }
}
