<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';

/* 
* /search.php?start=1&keyword=keyword here
*/
function search($keyWord='',$bid,$label='',$max=3,$start = 1){
    if(!empty($label)) {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary/-/'.$label.'?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);
    } else {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        echo $link_blog;
        echo '<br/>';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);        
    }
    if(!empty($keyWord)) {
    	$check = getPost($html,$keyWord);
    	if(!empty($check)) {
    		return $check;
    	} else {
    		return false;
    	}
    } else {
    	return $html;
    }    
}
function getPost($data,$keyWord){
	if(!empty($data->feed->entry)) {
		foreach ($data->feed->entry as $key => $entry) {
			$title = @$entry->title->{'$t'};
			$title = str_replace('[', '', $title);
			$title = str_replace(']', '', $title);
			$title = str_replace('(', '', $title);
			$title = str_replace(')', '', $title);
			$title = str_replace('||', '', $title);
			//$title = str_replace(',', '', $title);
			if (preg_match("/{$keyWord}/i", $title)) {
				$arr   = explode('-', $entry->id->{'$t'});
	        	$pid   = $arr[2];
				return array('title'=> $title,'pid'=>$pid);
			}
		}
	} else {
		return array('runout' => 1);
	}
	
}

$file = new file();
if(!empty($_GET['start'])) {
	$jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
	$getBlogId = $file->getFileContent($jsonTxt);
	$data = array();
	$search = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/search.csv';
	foreach ($getBlogId as $value) {
		$data[] = array($value->bid,0);  
	}
	$fp = fopen($search, 'w');
    foreach ($data as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);


	$getIdToSearch = $file->getFileContent($search,'csv');
	foreach ($getIdToSearch as $key => $row) {
		if($row[1] == 0) {
			$bid = $row[0];
		}
	}

	/*clean old post*/
	$searchFound = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/search_found.csv';
	unlink($searchFound);
	/*End clean old post*/
	$keyWordA = $_GET['keyword'];
	$keyWord = urlencode($keyWordA);
	echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/search.php?search=1&keyword='.$keyWord.'&bid=' . $bid . '&sart=1#1";</script>';
}

if(!empty($_GET['search']) && !empty($_GET['bid'])) {

	$blogID = $_GET['bid'];
	$keyWordA = $_GET['keyword'];
	$keyWord = urlencode($keyWordA);
	$start = $_GET['sart'];
	$post = search($keyWordA,$blogID,'',500,$start);
	if(empty($post) && empty($post['runout'])):?>
	<script type="text/javascript">
		setTimeout(function(){
			var setNum = 50;
			var moreNum = window.location.hash.substring(1);
			var setnew = 500 + Number(moreNum);
			window.location = "<?php echo base_url;?>blogger/search.php?search=1&bid=<?php echo $blogID;?>&keyword=<?php echo $keyWord;?>&sart="+setnew+"#" + setnew;
		}, 1000);

	</script>
	<?php elseif(!empty($post) && empty($post['runout'])):
		$searchFound = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/search_found.csv';
		$handle = fopen($searchFound, "a");
        fputcsv($handle, array($blogID,$post['pid'],$post['title']));
        fclose($handle);

        /*start save current id*/
        $data = array();
        $search = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/search.csv';
        $searchN = $file->getFileContent($search,'csv');
	    foreach ($searchN as $key => $row) {
	        $bid = $row[0];
	        $status = $row[1];	        
	        if(empty($status) && $bid == $blogID) {
	            $data[] = array($bid,1);
	        } else {
	            $data[] = array($bid,$status);
	        } 
	    }
	    $fp = fopen($search, 'w');
	    foreach ($data as $fields) {
	        fputcsv($fp, $fields);
	    }
	    fclose($fp);
	    /*end start save current id*/

	    /*start search new blog*/
	   	$getIdToSearch = $file->getFileContent($search,'csv');
		foreach ($getIdToSearch as $key => $row) {
			if($row[1] == 0) {
				$bid = $row[0];
			}
		}
		$keyWordA = $_GET['keyword'];
		$keyWord = urlencode($keyWordA);
		echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/search.php?search=1&bid=' . $bid . '&keyword='.$keyWord.'&sart=1#1";</script>';
	    /*End start search new blog*/
	else :
		$searchFound = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/search_found.csv';
		$handle = fopen($searchFound, "a");
        fputcsv($handle, array($blogID,'',''));
        fclose($handle);

		/*start save bid that not found id*/
        $data = array();
        $search = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/search.csv';
        $searchN = $file->getFileContent($search,'csv');
	    foreach ($searchN as $key => $row) {
	        $bid = $row[0];
	        $status = $row[1];	        
	        if(empty($status) && $bid == $blogID) {
	            $data[] = array($bid,1);
	        } else {
	            $data[] = array($bid,$status);
	        } 
	    }
	    $fp = fopen($search, 'w');
	    foreach ($data as $fields) {
	        fputcsv($fp, $fields);
	    }
	    fclose($fp);
	    /*end start save bid that not found id*/

	    /*start search new blog*/
	   	$getIdToSearch = $file->getFileContent($search,'csv');
		foreach ($getIdToSearch as $key => $row) {
			if($row[1] == 0) {
				$bid = $row[0];
			}
		}
		$keyWordA = $_GET['keyword'];
		$keyWord = urlencode($keyWordA);
		echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/search.php?search=1&bid=' . $bid . '&keyword='.$keyWord.'&sart=1#1";</script>';
	    /*End start search new blog*/
	 endif;?>
<?php 
}
;?>