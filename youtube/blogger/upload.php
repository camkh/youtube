<?php
include dirname(__FILE__) .'/../top.php';
// $imagePath = dirname(__FILE__) . '/../uploads/2018-08-05_13-28-53.jpg';
// $logoPath = dirname(__FILE__) . '/../uploads/watermark/web-logo.png';


function uploadImageFile() { // Note: GD library is required for this function

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $iWidth = $_POST['w'];
        $iHeight = $_POST['h']; // desired image result dimensions
        $iJpgQuality = 90;

        if ($_FILES) {

            // if no errors and size less than 250kb
            if (! $_FILES['image_file']['error'] && $_FILES['image_file']['size'] < 1000 * 3000) {
                if (is_uploaded_file($_FILES['image_file']['tmp_name'])) {

                    // new unique filename
                    $sTempFileName = dirname(__FILE__) . '/../uploads/cache/' . md5(time().rand());

                    // move uploaded file into cache folder
                    move_uploaded_file($_FILES['image_file']['tmp_name'], $sTempFileName);

                    // change file permission to 644
                    @chmod($sTempFileName, 0644);

                    if (file_exists($sTempFileName) && filesize($sTempFileName) > 0) {
                        $aSize = getimagesize($sTempFileName); // try to obtain image info
                        if (!$aSize) {
                            @unlink($sTempFileName);
                            return;
                        }

                        // check for image type
                        switch($aSize[2]) {
                            case IMAGETYPE_JPEG:
                                $sExt = '.jpg';

                                // create a new image from file 
                                $vImg = @imagecreatefromjpeg($sTempFileName);
                                break;
                            /*case IMAGETYPE_GIF:
                                $sExt = '.gif';

                                // create a new image from file 
                                $vImg = @imagecreatefromgif($sTempFileName);
                                break;*/
                            case IMAGETYPE_PNG:
                                $sExt = '.png';

                                // create a new image from file 
                                $vImg = @imagecreatefrompng($sTempFileName);
                                break;
                            default:
                                @unlink($sTempFileName);
                                return;
                        }

                        // create a new true color image
                        $vDstImg = @imagecreatetruecolor( $iWidth, $iHeight );

                        // copy and resize part of an image with resampling
                        imagecopyresampled($vDstImg, $vImg, 0, 0, (int)$_POST['x1'], (int)$_POST['y1'], $iWidth, $iHeight, (int)$_POST['w'], (int)$_POST['h']);

                        // define a result image filename
                        $sResultFileName = $sTempFileName . $sExt;

                        // output image to file
                        imagejpeg($vDstImg, $sResultFileName, $iJpgQuality);
                        @unlink($sTempFileName);


                        /* get some option*/

                        $hotImg = false;
                        if(!empty($_POST['vdotype'])) {
                            $hotImg = $_POST['vdotype'];
                        }
                        /* end get some option*/

                        /* watermark */
                        include dirname(__FILE__) .'/../library/ChipVN/Loader.php';
                        \ChipVN\Loader::registerAutoLoad();
                        $service  = 'Picasa';
                        $uploader = \ChipVN\Image_Uploader::factory($service);
                        $uploader->login('104724801112461222967', '0689989@Sn');

                        $imagePath = $sResultFileName;
                        $watermark = 1;
                        /* logo (right bottom, right center, right top, left top, .v.v.) */
                        if(!empty($_POST['watermark'])) {
                            $logoPosition = 'rb';
                            $logoPath = dirname(__FILE__) . '/../uploads/watermark/web-logo.png';
                            \ChipVN\Image::watermark($imagePath, $logoPath, $logoPosition);
                        }
                        
                        
                        if ($hotImg) {
                            $hotPos = 'rt';
                            $hot = dirname(__FILE__) . '/../uploads/watermark/hot/'.$hotImg.'.png';
                            \ChipVN\Image::watermark($imagePath, $hot, $hotPos);
                        }

                        $play = dirname(__FILE__) . '/../uploads/watermark/play-button.png';
                        $pPos = 'cc';
                        if ($watermark) {
                            \ChipVN\Image::watermark($imagePath, $play, $pPos);
                        }
                        $uploader->setAlbumId('6139092860158818081');
                        
                        $iamge = $uploader->upload($imagePath);
                        @unlink($sResultFileName);
                        return $iamge;
                    }
                }
            }
        }
    }
}

$sImage = uploadImageFile();
if(preg_match('/error/', $sImage)) {
    $data = array("error" => $sImage); 
} else {
    $data = array("image" => $sImage); 
}       
echo json_encode($data);