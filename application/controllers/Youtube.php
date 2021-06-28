<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Youtube extends CI_Controller {


 	        var $data;
 	        var $module_name = 'test';

			public function __construct()
			{
				parent::__construct();

				$this->load->database();

				$this->load->helper('url');
				$this->load->helper('array_helper');
				$this->load->helper('debug_helper');

				$this->load->library('ion_auth'); //인증 Library
                $this->load->library('session');

                $this -> load -> model('test', 'test');

                $this -> data = array(
                    "bootstrap_version" => "/assets/test/bootstrap",
                    "bootstrap_theme" => "/assets/bootstrap-3.3.3.6/css/flatly",//없으면 "" 있으면 superhero/
                    "jquery_version" => "/assets/js/2.2.0",
                    "module_name" => $this -> module_name,
                    "asset_directory" => "/assets/test", //없으면 "" 있으면 superhero/
                    "version" => "1.0.0"
                );

				if (!$this->ion_auth->logged_in())
				{
						redirect('/auth/login/?redirect='.urlencode(current_url()), 'refresh');
				}
			}


			public function index($itemId = null)
            {

                $data = $this -> data;

                require_once FCPATH . '/dp/youtube/vendor/autoload.php';

                if($itemId!=''){

                    $arr_result = $this -> ckmodel -> getTranslateInfo($itemId);
                    $title = $arr_result[0]['title'];
                    $tag = $arr_result[0]['itemTypeName'];
                    $video = $arr_result[0]['itemIcon'];

                }else{

                    $itemId = $this -> input -> get_post("itemId");
                    $title = $this -> input -> get_post("title");
                    $tag = $this -> input -> get_post("tag");
                    $video = $this -> input -> get_post("video");

                }


                $description = $this -> input -> get_post("description");
                $uploadSign = $this -> input -> get_post("uploadSign");

                $arr_youtube_info = $this->ckmodel->getYoutubeHistory($itemId);
                $arr_retail_info = $this->ckmodel->getRetailInfo($itemId);
                $cnt = count($arr_youtube_info);


                $data['itemId'] = isset($itemId)?$itemId:"";
                $data['title'] = isset($title)?$title:"";
                $data['tag'] = isset($tag)?$tag:"";
                $data['video'] = isset($video)?$video:"";


                if(!empty($arr_retail_info)){

                    $data['retail_link'] = isset($arr_retail_info[0]['retail_link'])?$arr_retail_info[0]['retail_link']:"";

                }else{

                    $data['retail_link'] = '';

                }

                if(!empty($arr_youtube_info)){

                    $data['description'] = isset($description)?$description:$arr_youtube_info[0]['description'];
                    $data['upload_url'] = isset($arr_youtube_info[0]['upload_url'])?$arr_youtube_info[0]['upload_url']:"";
                    $data['youtube_id'] = isset($arr_youtube_info[0]['youtube_id'])?$arr_youtube_info[0]['youtube_id']:"";

                }else{

                    $data['description'] = isset($description)?$description:"";
                    $data['upload_url'] = "";
                    $data['youtube_id'] = "";

                }



//                debug_var($description);
//                debug_var($arr_youtube_info);

                $arr_tag = array();
                if(!empty($tag)){
                    $arr_tag = explode(',',$tag);
                }

                $_SESSION['itemId'] = $itemId;
                $_SESSION['video'] = $video;
                $_SESSION['title'] = $title;
                $_SESSION['tag'] = $tag;
                $_SESSION['description'] = $description;
                $_SESSION['uploadSign'] = $uploadSign;

                $OAUTH2_CLIENT_ID = 'test';
                $OAUTH2_CLIENT_SECRET = 'test';

                $client = new Google_Client();

                $client->setClientId($OAUTH2_CLIENT_ID);

                $client->setClientSecret($OAUTH2_CLIENT_SECRET);

                $client->setScopes("https://www.googleapis.com/auth/youtube.upload");

                $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . '/Youtube', FILTER_SANITIZE_URL);

                $client->setRedirectUri($redirect);

                $youtube = new Google_Service_YouTube($client);

                $tokenSessionKey = 'token-' . $client->prepareScopes();

                if (isset($_GET['code'])) {

                    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
                        die('The session state did not match.');
                    }

                    $client->authenticate($_GET['code']);

                    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
                    $_SESSION['video'] = $video;

                    header('Location: ' . $redirect);

                }

                if (isset($_SESSION[$tokenSessionKey])) {

                    $client->setAccessToken($_SESSION[$tokenSessionKey]);

                }

//                debug_var($_SESSION);
//                exit;

                if(!($client->getAccessToken())){

                    $state = mt_rand();

                    $client->setState($state);

                    $_SESSION['state'] = $state;

                    $authUrl = $client->createAuthUrl();

                    $data['flag'] = 'url';
                    $data['authUrl'] = $authUrl;

                    $this -> load -> view('test/youtube_access',$data);

                }else{

                    if($_SESSION['video']==''){
                        echo "<script>alert('구글 로그인 인증이 완료되었습니다.');window.close();</script>";
                        exit;
                    }

                    if($uploadSign == 'ok'){

                        try{

                            $randFile = generateRandomSimpleString().".mp4";
                            $videoPath = FCPATH."/uploads/video_temp/".$randFile;

                            if ( !copy($_SESSION['video'], $videoPath) ) {
                                show_error("Missing file");
                            }

                            $snippet = new Google_Service_YouTube_VideoSnippet();
                            $snippet->setTitle($title);
                            $snippet->setDescription($description);
                            $snippet->setTags($arr_tag);

                            $snippet->setCategoryId("22");

                            $status = new Google_Service_YouTube_VideoStatus();
                            $status->privacyStatus = "public";


                            $video = new Google_Service_YouTube_Video();
                            $video->setSnippet($snippet);
                            $video->setStatus($status);

                            $chunkSizeBytes = 1 * 1024 * 1024;

                            $client->setDefer(true);


                            $insertRequest = $youtube->videos->insert("status,snippet", $video);


                            $media = new Google_Http_MediaFileUpload(
                                $client,
                                $insertRequest,
                                'video/*',
                                null,
                                true,
                                $chunkSizeBytes
                            );

                            $media->setFileSize(filesize($videoPath));

                            $status = false;
                            $handle = fopen($videoPath, "rb");

                            while (!$status && !feof($handle)) {
                                $chunk = fread($handle, $chunkSizeBytes);
                                $status = $media->nextChunk($chunk);
                                //debug_var($status);
                            }

                            fclose($handle);

                            $client->setDefer(false);

                            $data['id'] = isset($status['id'])?$status['id']:"";

                            $arr_data = array(
                                "itemId" => $_SESSION['itemId'],
                                "youtube_id" => $status['id'],
                                "title" => $_SESSION['title'],
                                "tag" => $_SESSION['tag'],
                                "video" => $_SESSION['video'],
                                "description" => $_SESSION['description'],
                                "upload_url" => "https://www.youtube.com/watch?v={$status['id']}",
                                "create_date" => date("Y-m-d H:i:s")
                            );

                            $data['result_url'] = "https://www.youtube.com/watch?v={$status['id']}";

                            $this -> ckmodel -> insertYoutubeData($arr_data);


                        } catch (Google_Service_Exception $e) {
                            $data['login_msg'] = '다시 로그인 해주세요.';
                            echo $data['login_msg'];
                            //echo $e->getMessage();
                        } catch (Google_Exception $e) {
                            $data['login_msg'] = '다시 로그인 해주세요.';
                            echo $data['login_msg'];
                            //echo $e->getMessage();
                        }

                        $_SESSION[$tokenSessionKey] = $client->getAccessToken();

                        $data['flag'] = 'success';

                        $this -> load -> view('test/youtube_access',$data);

                    }else{

                        $this -> load -> view('test/youtube_result',$data);


                    }


                    // 토큰 정보가 없을때
                    //$data['flag'] = 'client';
                    //$this -> load -> view('test/youtube_access',$data);

                }



            }

}