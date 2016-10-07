<?php
/* Overview
 * --------
 * Class for methods to generate a PDF document from HTML content 
 *
 * - currently only implemented method to generate PDF document from 
 *   passed string or array.
 *
 */
 
/* How to use this class:
 * - Create an instance of the class
 * - Call one of the simple access methods, such as serve_pdf_from_string
 */

class Html2pdf  
{
    protected $binary_loc  = "/usr/local/bin/";
    protected $binary_base = "wkhtmlto";    
    protected $output_ext  = NULL;
    protected $binary_end  = NULL;
    protected $global_args = "-q"; // quiet
    protected $proc = NULL;

    public $displayInline = FALSE;
    public $exitStatus = NULL;

    /* This function generates a PDF download from the passed HTML content 
     * 
     * IMPORTANT - you must not have written any output before invoking this
     *             method.
     */
    public function serve_pdf_from_string($html_string, 
                                          $download_filename=FALSE)
    {
        return $this->serve_content_from_string($html_string,
                                                "pdf",
                                                $download_filename);
    }

    /* Example of how to generate other content formats */
    public function serve_jpg_from_string($html_string, 
                                          $download_filename=FALSE)
    {
        return $this->serve_content_from_string($html_string,
                                                "jpg",
                                                $download_filename);
    }

    public function serve_content_from_string($html_string, 
                                              $type_extension, 
                                              $download_filename=FALSE)
    {
        $rc = FALSE;

        $this->output_ext = $type_extension;

        if ($download_filename === FALSE)
        {
            $download_filename = "output." . $type_extension;
        }
        
        if (!$this->exec_piped_in_out($pipes))
        {
            $this->fail_whale_bye("Unable to create wkhtml piped process.");
        }
        else            
        {
            // Push the passed HTML string to the executing process
            $nbytes = fwrite($pipes[0], $html_string);
            fclose($pipes[0]);

            $pdf_content = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // Close the executing process and get the exit code
            // - if executed correctly then serve the pdf           
            if ($this->close_exec())
            {
                // End output buffering and ditch anything that has already 
                // been sent
                ob_end_clean();

                // Start buffering again
                ob_start();

                $mimetype = finfo_file(finfo_open(FILEINFO_MIME_TYPE), 
                                       $download_filename);

                // Send headers to indicate content type
                header('Content-type: ' . $mimetype);

                // Tell the browser to download the file, rather than attempt to 
                // display inline. 
                if ($this->displayInline)
                {
                    $disposition = "inline";
                }
                else
                {
                    $disposition = "attachment";
                }                
                header('Content-Disposition: ' . $disposition . '; filename="' . $download_filename . '"');

                // Tell the browser not to cache the result                
                header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', FALSE);
                header('Pragma: no-cache');
                
                // Send the PDF output
                if (file_put_contents("php://output", $pdf_content) > 0)
                {
                    $rc = TRUE;
                }

                // End output buffering, send the file to the browser
                ob_end_flush();
                // Close the output stream
                flush();
            }
            else
            {
                $this->fail_whale_bye("Command " . 
                                      $this->binary_loc . 
                                      $this->binary_base.
                                      $this->binary_end . " returned exit status " . $this->exitStatus . "(" . htmlspecialchars($errors) . ")");
            }
        }
        return $rc;
    }

    protected function exec_piped_in_out(& $pipes)
    {
        $this->proc = NULL;
        $this->exitStatus = NULL;

        $this->select_binary();
        
        /* Note the read (r) and write (w) denotation is relative to the 
         * executing process, so read (r) is read by the process, and 
         * written from within php; write (w) is written by the process, 
         * and read from within php.
         */
        $pipe_descriptors = array(0 => array("pipe", "r"), /* stdin */
                                  1 => array("pipe", "w"), /* stdout */
                                  2 => array("pipe", "w")  /* stderr */);
       
        // Pass dash (-) to tell wkhtmltopdf to use stdin for input
        // and a second dash (-) to tell it to use stdout for output
        $cmd_args = $this->global_args . " - -";
        
        try 
        {
            $this->proc = proc_open($this->binary_loc . 
                                    $this->binary_base.
                                    $this->binary_end . " " . $cmd_args,
                                    $pipe_descriptors,
                                    $pipes);

        }
        catch (Exception $e)
        {
            $this->fail_whale_bye("Unable to open process: " . $e->getMessage()); 
        }
        
        if (is_resource($this->proc))
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    protected function close_exec()
    {
        $this->exitStatus = proc_close($this->proc);
        if ($this->exitStatus === 0)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /* Select wkhtmltoimage or wkhtmltopdf based on file extension */
    protected function select_binary()
    {
        $rc  = FALSE;
        $end = FALSE;

        switch ($this->output_ext)
        {
            case NULL:
            case "pdf":
                $this->binary_end = "pdf";
                $rc = TRUE;
                break;
                
            default:
                $this->binary_end = "image";
                $rc = TRUE;
        }
 
        return $rc;
    }

    protected function fail_whale_bye($message)
    {
        header("HTTP/1.0 500 Internal Server Error");
        echo "<h1>500 Internal Server Error</h1>";
        echo "<p>Fatal error: " . $message . "</p>";
        throw new Exception ("Fatal error: " . $message);
        return TRUE;
    }


}
/* Note it is VERY important not to have and spurious whitespace or newlines
 * AFTER the closing ?> tag, or they will prepend the PDF and potentially
 * create a corrupted file
 */
?>