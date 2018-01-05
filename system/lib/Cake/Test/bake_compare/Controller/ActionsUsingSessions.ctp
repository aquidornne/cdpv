
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
				$this->Session->setFlash(__('The bake article has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The bake article could not be saved. Please, try again.'));
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
				$this->Session->setFlash(__('The bake article has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The bake article could not be saved. Please, try again.'));
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
			$this->Session->setFlash(__('The bake article has been deleted.'));
		} else {
			$this->Session->setFlash(__('The bake article could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
