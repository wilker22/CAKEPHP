<?php
	
class PostsController extends AppController {

    public $helpers = array ('Html','Form');
    public $name = 'Posts';
    public $components = array('Session');

    public function beforeFilter() {
            parent::beforeFilter();
            $this->Auth->allow('add', 'logout'); // Letting users register themselves
    }

    //metodo mostra os registros
    function index() {
        $this->set('posts', $this->Post->find('all')); // método find('all') busca todo os registros no banco tabela posts
    }

    //metodo para visualizar conforme o id
    public function view($id = null) {
        $this->Post->id = $id;
        $this->set('post', $this->Post->read()); // método read()) busca os registros conforme o id 
        //passado e set o id por parametro no banco tabela posts
    }

    //metodo para adicionar registros
    public function add() {
        if ($this->request->is('post')) {
            //retorna qualquer coluna do usuario logado
            $this->request->data['Post']['user_id'] = $this->Auth->user('id'); //Added this line
            if ($this->Post->save($this->request->data)) {
                $this->Session->setFlash('Your post has been saved.');//o método SessionComponent::setFlash()
                // do componente SessionComponent para definir
                // uma variável de sessão com uma mensagem a ser exibida na página depois de ser redirecionada. 
                $this->redirect(array('action' => 'index'));
            }
        }
    }

    //metodo para editar registros
    function edit($id = null) {
	    $this->Post->id = $id;
	    if ($this->request->is('get')) {//pega por get
	        $this->request->data = $this->Post->read();
	    } else {
	        if ($this->Post->save($this->request->data)) {
	            $this->Session->setFlash('Your post has been updated.');
	            $this->redirect(array('action' => 'index'));
	        }
	    }
	}


	//metodo para deletar registros por ID
	function delete($id) {
	    if (!$this->request->is('post')){//pega por post 
	        throw new MethodNotAllowedException();
	    }
	    if ($this->Post->delete($id)) {
	        $this->Session->setFlash('The post with id: ' . $id . ' has been deleted.');
	        $this->redirect(array('action' => 'index'));
	    }
	}
    // metodo par autorizacao Nós estamos sobreescrevendo a chamada do isAuthorized() do AppController e internamente 
    //verificando se na classe pai já é usuário autoriado. Se ele não for, então só lhe permite acesso a ação add
    public function isAuthorized($user) {
        if (!parent::isAuthorized($user)) {
            if ($this->action === 'add') {
                // All registered users can add posts
                return true;
            }
            if (in_array($this->action, array('edit', 'delete'))) {
                $postId = $this->request->params['pass'][0];
                return $this->Post->isOwnedBy($postId, $user['id']);
            }
        }
        return false;
    }

    //metodo para logout
    public function logout() {
         $this->redirect($this->Auth->logout());
    }
}

?>