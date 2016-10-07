<?php

class Sys_file extends CI_Model {
	
	#Constructor
	function Sys_file()
	{
		parent::__construct();
		$this->load->model('file_upload','libfileobj');
	}
	
	var $processing_errors;
	
	# Perfom file upload customised for NHSN work
	function perfom_file_upload($file_object, $form_field_object, $filename, 
		$upload_directory = UPLOAD_DIRECTORY, $allowed_extensions = ALLOWED_EXTENSIONS, $maximum_file_name_length = MAXIMUM_FILE_NAME_LENGTH)
	{	
		# Create the upload directory if it does not exist yet
		if (!is_dir($upload_directory)) {
			mkdir ($upload_directory);
		}
		
		# The directory to upload the file to
		$file_object->upload_dir = $upload_directory;
		# Specify the allowed extensions here
		$file_object->extensions = explode(",",$allowed_extensions);
		# Change this value to fit your field length in your database (standard 100)
		$file_object->max_length_filename = $maximum_file_name_length; 
		# To rename the files, we set this to true
		$file_object->rename_file = true;
		# The temporary file where files are uploaded
		$file_object->the_temp_file = $form_field_object['tmp_name'];
		# The file name before uploading
		$file_object->the_file = $form_field_object['name'];
		# Get any errors encountered during the process of uploading
		$file_object->http_error = $form_field_object['error'];
		# The file is given this name after upload. It is assumed that each event has one file.
		$new_file_name = $filename;
		
		#If upload is successful pick the new file name and proceed to process the rest of the form
		if ($file_object->upload($new_file_name)) 
		{	
			$filename = $new_file_name.strtolower(strrchr($form_field_object['name'],"."));
		}
		else
		{
			$filename = "";
		}
		
		return array('filename' => $filename, 'errors' => $file_object->show_error_string());
	}
	
	
	
	function local_file_upload($file_obj, $file_stamp, $directory_name='', $returntype='filename', $counter='')
	{
		$file_url = '';
		#If it is a multiple file entry, get the current file being uploaded and recreate the file array object
		if($counter != '')
		{
			$actual_file_obj = array('name'=>$file_obj['name'][$counter], 'type'=>$file_obj['type'][$counter], 'tmp_name'=>$file_obj['tmp_name'][$counter], 'error'=>$file_obj['error'][$counter], 'size'=>$file_obj['size'][$counter]);
		} else {
			$actual_file_obj = $file_obj;
		}
		
		
		# If a file has been uploaded and there are no errors process it before continuing
		# Upload the file and return the results of the upload
		$processing_results = $this->perfom_file_upload($this->libfileobj, $actual_file_obj, $file_stamp, UPLOAD_DIRECTORY.$directory_name."/", implode(',', $this->session->userdata('local_allowed_extensions')));
		
		$this->processing_errors = $processing_results['errors'];
		# Will be saved in the database as the event's document file name
		$file_url = $processing_results['filename']; 
		
		if($returntype == 'filename'){
			return $file_url;
		}
		else
		{
			return array('fileurl'=>$file_url, 'formfileobj'=>$actual_file_obj);
		}
	}
	
	
	
	
	#Function to breakup a file into smaller child files
	function break_up_file($file_url, $no_of_rows_per_array)
	{
		$file_array = array();
		
		if (($handle = fopen($file_url, "r")) !== FALSE) 
		{
			$chunks = array_chunk(file($file_url), $no_of_rows_per_array);
			
			$count = 1;
			foreach($chunks AS $file_chunk)
			{
				$child_file_url = str_replace('.csv', '_'.$count.'.csv', $file_url);
				array_push($file_array, array('file'=>substr($child_file_url, (strrpos($child_file_url, '/')+1) ), 'partno'=>$count));
				
				$fh = fopen($child_file_url, 'w+');
				foreach($file_chunk AS $chunkrow){
					fwrite($fh, $chunkrow);
				}
				
				fclose($fh);
				$count++;
				
			}
		}
		
		return $file_array;
	}
}

?>