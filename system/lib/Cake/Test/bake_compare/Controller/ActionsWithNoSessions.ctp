
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->BakeEvent->recursive = 0;
		$this->set('bakeEvents', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->BakeEvent->exists($id)) {
			throw new NotFoundException(__('Invalid bake article'));
		}
		$options = array('conditions' => array('BakeEvent.' . $this->BakeEvent->primaryKey => $id));
		$this->set('bakeEvent', $this->BakeEvent->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->BakeEvent->create();
			if ($this->BakeEvent->save($this->request->data)) {
				return $this->flash(__('The bake article has been saved.'), array('action' => 'index'));
			}
		}
		$bakeTags = $this->BakeEvent->BakeTag->find('list');
		$this->set(compact('bakeTags'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->BakeEvent->exists($id)) {
			throw new NotFoundException(__('Invalid bake article'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->BakeEvent->save($this->request->data)) {
				return $this->flash(__('The bake article has been saved.'), array('action' => 'index'));
			}
		} else {
			$options = array('conditions' => array('BakeEvent.' . $this->BakeEvent->primaryKey => $id));
			$this->request->data = $this->BakeEvent->find('first', $options);
		}
		$bakeTags = $this->BakeEvent->BakeTag->find('list');
		$this->set(compact('bakeTags'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->BakeEvent->id = $id;
		if (!$this->BakeEvent->exists()) {
			throw new NotFoundException(__('Invalid bake article'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->BakeEvent->delete()) {
			return $this->flash(__('The bake article has been deleted.'), array('action' => 'index'));
		} else {
			return $this->flash(__('The bake article could not be deleted. Please, try again.'), array('action' => 'index'));
		}
	}
