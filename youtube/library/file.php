<?php
class file {
	public function json($upload_path,$file_name, $list = array(),$do='update')
    {
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0700);
        }
        if (!file_exists($upload_path.$file_name)) {
            $f = fopen($upload_path.$file_name, 'w');
            $fwrite = fwrite($f, json_encode($list));
            fclose($f);
        } else {
            $f = fopen($upload_path.$file_name, 'w');
            $fwrite = fwrite($f, json_encode($list));
            fclose($f);
        }
        if ($do == 'update') {
            $f = fopen($upload_path.$file_name, 'w');
            $fwrite = fwrite($f, json_encode($list));
            fclose($f);
        } else if ($do == 'delete') {
            unlink($upload_path.$file_name);
            $f = fopen($upload_path.$file_name, 'w');
            $fwrite = fwrite($f, json_encode($list));
            fclose($f);
        }
        if ($fwrite === false) {
            return TRUE;
        } else {
            return false;
        }
    }

    public function uploadMedia($file_path)
       {
        $imgName = $file_path;
        $authToken = $_SESSION['tokenSessionKey'];
        $client_id = '51d22a7e4b628e4';

        $filetype = mime_content_type($file_path);
        /*resize image*/
        $maxDim = 1200;
        $file_name = $imgName;
        list($width, $height, $type, $attr) = getimagesize( $file_name );
        if ( $width < $maxDim || $height < $maxDim ) {
            $target_filename = $file_name;
            $ratio = $width/$height;
            if( $ratio > 1) {
                $new_width = $maxDim;
                $new_height = $maxDim/$ratio;
            } else {
                $new_width = $maxDim*$ratio;
                $new_height = $maxDim;
            }
            $src = imagecreatefromstring( file_get_contents( $file_name ) );
            $dst = imagecreatetruecolor( $new_width, $new_height );
            imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
            imagedestroy( $src );
            imagejpeg( $dst, $target_filename ); // adjust format as needed
            imagedestroy( $dst );
        }
        /*end resize image*/
        /*upload to imgur.com*/
        $image = file_get_contents($imgName);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Client-ID $client_id" ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'image' => base64_encode($image) ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $reply = curl_exec($ch);
        curl_close($ch);
        $reply = json_decode($reply);
        if(!empty($reply->data->link)) {
            return $reply->data->link;
        } else {
            return false;
        }
        /*End upload*/
    }
    public function getFileContent($file,$type='json')
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $data = [];
        switch ($ext) {
            case 'json':
                $str = file_get_contents($file);
                $data = json_decode($str);
                break;            
            case 'csv':
                if (!file_exists($file)) {
                    return false;
                }
                $fp = fopen($file,'r') or die("can't open file");
                while($csv_line = fgetcsv($fp,1024)) {
                    if($type == 'json') {
                        $data[] = (object) array('bid'=>$csv_line[0],'bname'=>$csv_line[1]);
                    } else {
                        $data[] = $csv_line;
                    }                    
                }
                break;
        }        
        return $data;
    }

    public function csv($upload_path,$file_name, $list = array())
    {
        $fp = fopen($upload_path . $file_name, 'w');

    }
}