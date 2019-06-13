<?php
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);
//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
	$username = $request->get['username'];
	if(!empty($ws->fd)){
		foreach ($ws->fd as $fd=>$value) {
			$ws->push($fd, json_encode(['type'=>'message','data'=>'系统:'.$username.", 进来了\n",'time'=>date('Y-m-d H:i:s',time())]));
		}
	}
    $ws->fd[$request->fd] = $username;
    $ws->push($request->fd, json_encode(['type'=>'message','data'=>"系统:hello, welcome\n",'time'=>date('Y-m-d H:i:s',time())]));
    
   	foreach ($ws->fd as $fd=>$username) {
		$ws->push($fd,json_encode(['type'=>'list','data'=>$ws->fd]));
	}
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
	foreach ($ws->fd as $fd=>$username) {
		$ws->push($fd, json_encode(['type'=>'message','data'=>$ws->fd[$frame->fd].": {$frame->data}\n",'time'=>date('Y-m-d H:i:s',time())]));
	}
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
	unset($ws->fd[$fd]);
	if(count($ws->fd) > 1){
		foreach ($ws->fd as $fdk=>$username) {
			$ws->push($fdk, json_encode(['type'=>'message','data'=>'系统:'.$username.", 离开了\n",'time'=>date('Y-m-d H:i:s',time())]));
			$ws->push($fdk, json_encode(['type'=>'list','data'=>$ws->fd]));
		}
	}
	
});


$ws->start();

