<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GraphicDetail;
use App\Http\Resources\GraphicList;
use App\Http\Resources\MaterialDetail;
use App\Http\Resources\MaterialList;
use App\Http\Resources\QuestionDetail;
use App\Http\Resources\QuestionList;
use App\Http\Resources\ReportDetail;
use App\Http\Resources\ReportList;
use App\Models\Graphic;
use App\Models\Material;
use App\Models\MaterialLike;
use App\Models\Question;
use App\Models\QuestionComment;
use App\Models\QuestionLike;
use App\Models\Report;
use App\Models\ReportLike;
use Illuminate\Http\Request;


class MaterialController extends CommonController
{
    /**
     * 百问百答列表
     */
    public function questionList(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $keyword = $request->input('keyword');
        $question = new Question();
        if ($keyword){
           $question = $question->where('title','like','%'.$keyword.'%');
        }
        $question = $question->with(['like'=>function($query) use($user_id){
            $query->where('user_id',$user_id);
        }])->where('is_show',1)->orderBy('sort','asc')->get();
        $list = [];
        if ($question){
            $list = QuestionList::collection($question);
        }
        return jsonSuccess($list);
    }

    /**
     * 百问百答详情
     */
    public function questionDetail(Request $request)
    {
        $question_id = $request->input('question_id');
        if ($question_id){
            $question = new Question();
            $where = [
                'id' => $question_id,
            ];
            $result = $question->getDetail($where);
            if ($result){
                //组装评论
                $commentModel = new QuestionComment();
                $comment_where = [
                    'question_id' => $question_id
                ];
                $comment = $commentModel->getList($comment_where);
                $result['comments'] = [];
                if ($comment){
                    $arr = $comment->toArray();
                    $result['comments'] = $this->getTree($arr);
                }
                $detail = QuestionDetail::make($result);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数获取失败');

    }

    /**
     * 百问百答点赞
     */
    public function questionLike(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $question_id = $request->input('question_id');
        if ($question_id){

            $likeModel = new QuestionLike();
            $where = [
                'question_id' => $question_id,
                'user_id' => $user_id,
            ];
            $like = $likeModel->getDetail($where);
            if ($like){
                $result = $likeModel->unlike($where);
                if ($result){
                   return jsonSuccess([],'取消点赞成功');
                }
            }else{
                $result = $likeModel->like($where);
               if ($result){
                   return jsonSuccess([],'点赞成功');
               }
            }
            return jsonError('点赞失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 百问百答提问
     */
    public function questionComment(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $question_id = $request->input('question_id');
        $content = $request->input('content');
        $parent_id = $request->input('parent_id');
        if ($question_id){
            if ($content){
                $commentModel = new QuestionComment();
                $arr = [
                    'content' => $content,
                    'comment_time' => time(),
                    'user_id' => $user_id, //$this->getUserId(),
                    'question_id' => $question_id,
                    'parent_id' => $parent_id?$parent_id:0,
                ];
                $result = $commentModel->addComment($arr);
                if ($result){
                    return jsonSuccess('','评论成功');
                }
                return jsonError('评论失败');
            }
            return jsonError('请填写内容');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 图文专栏列表
     */
    public function graphicList()
    {
        $graphic = new Graphic();
        $result = $graphic->getList();
        $list = [];
        if ($result){
            $list = GraphicList::collection($result);
        }
        return jsonSuccess($list);
    }

    /**
     * 图文专栏详情
     */
    public function graphicDetail(Request $request)
    {
        $graphic_id = $request->input('graphic_id');
        if ($graphic_id){
            $graphic = new Graphic();
            $result = $graphic->where('id',$graphic_id)->first();
            if ($result){
                $detail = GraphicDetail::make($result);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 宣传报道列表
     */
    public function reportList(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $report = new Report();
        $where = [
            'user_id' => $user_id,//$this->getUserId()
        ];
        $result = $report->getList($where);
        $list = [];
        if ($result){
            $list = ReportList::collection($result);
        }
        return jsonSuccess($list);
    }

    /**
     * 宣传报道详情
     */
    public function reportDetail(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $report_id = $request->input('report_id');
        if ($report_id){
            $report = new Report();
            $where = [
                'id' => $report_id
            ];
            $where2 = [
                'user_id' => $user_id,//$this->getUserId()
            ];
            $result = $report->getDetail($where,$where2);
            if ($result){
                $detail = ReportDetail::make($result);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 宣传报道点赞
     */
    public function reportLike(Request $request)
    {
        $report_id = $request->input('report_id');
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        if ($report_id){

            $likeModel = new ReportLike();
            $where = [
                'report_id' => $report_id,
                'user_id' => $user_id,
            ];
            $like = $likeModel->getDetail($where);
            if ($like){
                $result = $likeModel->unlike($where);
                if ($result){
                    return jsonSuccess([],'取消点赞成功');
                }
            }else{
                $result = $likeModel->like($where);
                if ($result){
                    return jsonSuccess([],'点赞成功');
                }
            }
            return jsonError('点赞失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 素材圈列表
     */
    public function materialList(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $material = new Material();

        $where = [
            'user_id' => $user_id,//$this->getUserId()
        ];
        $result = $material->getList($where);
        $list = [];
        if ($result){
            $list = MaterialList::collection($result);
        }
        return jsonSuccess($list);
    }

    /**
     * 素材圈详情
     */
    public function materialDetail(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $material_id = $request->input('material_id');
        if ($material_id){
            $where = [
                'id' => $material_id,
                'is_show' => 1
            ];
            $where2 = [
                'user_id' => $user_id,//$this->getUserId()
            ];
            $material = new Material();
            $result = $material->getDetail($where,$where2);
            if ($result){
                $detail = MaterialDetail::make($result);
                return jsonSuccess($detail);
            }
            return jsonError('数据获取失败');
        }
        return jsonError('参数获取失败');
    }

    /**
     * 素材圈点赞
     */
    public function materialLike(Request $request)
    {
        //判断用户是否登录
        $user_id = $request->input('user_id');
        if (!$user_id){
            return jsonLoginError();
        }

        $material_id = $request->input('material_id');
        if ($material_id){

            $likeModel = new MaterialLike();
            $where = [
                'material_id' => $material_id,
                'user_id' => $user_id,
            ];
            $like = $likeModel->getDetail($where);
            if ($like){
                $result = $likeModel->unlike($where);
                if ($result){
                    return jsonSuccess([],'取消点赞成功');
                }
            }else{
                $result = $likeModel->like($where);
                if ($result){
                    return jsonSuccess([],'点赞成功');
                }
            }
            return jsonError('点赞失败');
        }
        return jsonError('参数获取失败');
    }
}
