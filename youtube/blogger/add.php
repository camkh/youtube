<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
function checkDuplicate($bid,$label='',$max=3,$start = 1){
    if(!empty($label)) {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary/-/'.$label.'?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);
    } else {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);        
    } 
    return $html;
}
function getBlogId()
{
   $jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
   $fp = fopen($jsonTxt,'r') or die("can't open file");
   $data = [];
    while($csv_line = fgetcsv($fp,1024)) {
        $data[] = array(
            'bid' => $csv_line['0'],
            'bname' => $csv_line['1']
        );
    }
    return (object) $data;
}
function sitekmobilemovie($param = '', $title = '', $thumb = '', $post_id = '', $videotype = '') {
}
/* form */
if (!empty($_POST['submit'])) {
    include dirname(__FILE__) .'/../library/blogger.php';
    $videotype = '';
    $id = '';
    $xmlurl    = @$_POST['blogid'];
    $thumb     = @$_POST['imageid'];
    $label = @$_POST['label'];
    $title     = @$_POST['title'];
    if (preg_match('/kmobilemovie/', $xmlurl)) {
        $xmlurl = sitekmobilemovie($xmlurl, $title, $thumb, $id, $label);
    }
    $site = new blogger();
    $list = $site->getfromsiteid($xmlurl, $id, $thumb, $title, $label);
    $upload_path = dirname(__FILE__) . '/../uploads/user/'.$_SESSION['user_id'] . '/';
    $file_name = 'post.json';
    $file = new file();
    $csv = $file->json($upload_path,$file_name, $list);
    //$code = get_from_site_id($xmlurl, $id, $thumb, $title, '', $videotype); 

    if (!empty($id)) {
        //redirect(base_url() . 'post/getcode/edit/' . $id);
        header('Location: ' . base_url . '/blogger/post.php?do=edit');
    } else {
        header('Location: ' . base_url . '/blogger/post.php?do=add');
    }
}
/* end form */
?>
<!doctype html>
<html>
<head>
  <?php include __DIR__.'/../head.php';?>
<title>Set and retrieve localized metadata for a channel</title>
<script type="text/javascript" src="<?php echo base_url;?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
</head>
<body>
  <?php include __DIR__.'/../header.php';?>
      <div id="container">
        <div id="content">
            <div class="container">
                <?php include __DIR__.'/../leftside.php';?>
                <div class="page-header">
                    <div class="page-title">
                        <h3>Auto Post to Blogger and Facebook
                        </h3>
                    </div>
                </div>
                

                <!-- data -->
                <div class="col-md-12">
            <div class="widget box">
                <div class="widget-header">
                    <h4>
                        <i class="icon-reorder">
                        </i>
                        Add New Post
                    </h4>                     
                    <div class="toolbar no-padding">
                    </div>
                </div>
                <div class="widget-content">
                    <form method="post" id="validate" class="form-horizontal row-border">
                        <div class="form-group">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="imageid">Title</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="title" id="title" />
                                    </div>                         
                                </div>                         
                            </div>                         
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="imageid">Image</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="imageid" id="imageid" />
                                    </div>                         
                                </div>                         
                            </div>                         
                        </div>
                        <div class="form-group">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="imageid">Type</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="label" placeholder="Label here"  required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" class="form-control required" name="blogid" required/>
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-info" name="submit" value="Submit">
                                            Get code                           
                                        </button>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
                <!-- End data -->
            </div>
        </div>
    </div> 
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/lodash.compat.min.js"></script> 
</body>
</html>