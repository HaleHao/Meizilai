 <?php

function jsonSuccess($data = [],$msg = 'success',$code = 200,$status = 1)
{
    return response()->json([
        'msg' => $msg,
        'code' => $code,
        'status' => $status,
        'data' => $data
    ]);
}

function jsonError($msg = 'error',$code = 20001,$status = 0,$data = [])
{
    return response()->json([
        'msg' => $msg,
        'code' => $code,
        'status' => $status,
        'data' => $data
    ]);
}
 function jsonMsg($msg = 'success',$code = 200,$status = 1)
 {
     return response()->json([
         'msg' => $msg,
         'code' => $code,
         'status' => $status,
     ]);
 }


 function jsonLoginError($msg = '请先登录',$code = 40005,$status = 0)
 {
     return response()->json([
         'msg' => $msg,
         'code' => $code,
         'status' => $status,
     ]);
 }

 function jsonStoreError($msg = '请先登录',$code = 40006,$status = 0)
 {
     return response()->json([
         'msg' => $msg,
         'code' => $code,
         'status' => $status,
     ]);
 }