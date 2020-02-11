<?php
/**
 * CategoryController
 * @package admin-post-category
 * @version 0.0.1
 */

namespace AdminPostCategory\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibForm\Library\Combiner;
use LibPagination\Library\Paginator;
use PostCategory\Model\{
    PostCategory as PCategory,
    PostCategoryChain as PCChain
};

class CategoryController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['post', 'category']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_post_category)
            return $this->show404();

        $category = (object)[];

        $id = $this->req->param->id;
        if($id){
            $category = PCategory::getOne(['id'=>$id]);
            if(!$category)
                return $this->show404();
            $params = $this->getParams('Edit Post Category');
        }else{
            $params = $this->getParams('Create New Post Category');
        }

        $form           = new Form('admin.post-category.edit');
        $params['form'] = $form;

        $c_opts = [
            'meta'   => [null, null, 'json'],
            'parent' => [null, null, 'format', 'all', 'name', 'parent']
        ];

        $combiner = new Combiner($id, $c_opts, 'post-category');
        $category = $combiner->prepare($category);

        $params['opts'] = $combiner->getOptions();

        if($id){
            // remove self from parent
            foreach($params['opts']['parent'] as $index => $parent){
                if($parent->value == $id){
                    unset($params['opts']['parent'][$index]);
                    break;
                }
            }
        }

        if(!($valid = $form->validate($category)) || !$form->csrfTest('noob'))
            return $this->resp('post/category/edit', $params);

        $valid = $combiner->finalize($valid);
        if(!isset($valid->parent))
            $valid->parent = 0;

        if($id){
            if(!PCategory::set((array)$valid, ['id'=>$id]))
                deb(PCategory::lastError());
        }else{
            $valid->user = $this->user->id;
            if(!PCategory::create((array)$valid))
                deb(PCategory::lastError());
        }

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'post-category',
            'original' => $category,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminPostCategory');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_post_category)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;

        list($page, $rpp) = $this->req->getPager(25, 50);

        $categories = PCategory::get($cond, $rpp, $page, ['name'=>true]) ?? [];
        if($categories)
            $categories = Formatter::formatMany('post-category', $categories, ['user', 'parent']);

        $params               = $this->getParams('Post Category');
        $params['categories'] = $categories;
        $params['form']       = new Form('admin.post-category.index');

        $params['form']->validate( (object)$this->req->get() );

        // pagination
        $params['total'] = $total = PCategory::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminPostCategory'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('post/category/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_post_category)
            return $this->show404();

        $id       = $this->req->param->id;
        $category = PCategory::getOne(['id'=>$id]);
        $next     = $this->router->to('adminPostCategory');
        $form     = new Form('admin.post-category.index');

        if(!$category)
            return $this->show404();

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'post-category',
            'original' => $category,
            'changes'  => null
        ]);

        PCategory::remove(['id'=>$id]);
        PCategory::set(['parent'=>0], ['parent'=>$id]);
        PCChain::remove(['category'=>$id]);
        
        $this->res->redirect($next);
    }
}