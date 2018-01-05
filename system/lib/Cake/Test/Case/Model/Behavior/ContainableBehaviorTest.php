<?php
/**
 * ContainableBehaviorTest file
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Case.Model.Behavior
 * @since         CakePHP(tm) v 1.2.0.5669
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

require_once dirname(dirname(__FILE__)) . DS . 'models.php';

/**
 * ContainableTest class
 *
 * @package       Cake.Test.Case.Model.Behavior
 */
class ContainableBehaviorTest extends CakeTestCase {

/**
 * Fixtures associated with this test case
 *
 * @var array
 */
	public $fixtures = array(
		'core.article', 'core.article_featured', 'core.article_featureds_tags',
		'core.articles_tag', 'core.attachment', 'core.category',
		'core.comment', 'core.featured', 'core.tag', 'core.user',
		'core.join_a', 'core.join_b', 'core.join_c', 'core.join_a_c', 'core.join_a_b'
	);

/**
 * Method executed before each test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('User');
		$this->Event = ClassRegistry::init('Event');
		$this->Tag = ClassRegistry::init('Tag');

		$this->User->bindModel(array(
			'hasMany' => array('Event', 'EventFeatured', 'Comment')
		), false);
		$this->User->EventFeatured->unbindModel(array('belongsTo' => array('Category')), false);
		$this->User->EventFeatured->hasMany['Comment']['foreignKey'] = 'article_id';

		$this->Tag->bindModel(array(
			'hasAndBelongsToMany' => array('Event')
		), false);

		$this->User->Behaviors->load('Containable');
		$this->Event->Behaviors->load('Containable');
		$this->Tag->Behaviors->load('Containable');
	}

/**
 * Method executed after each test
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Event);
		unset($this->User);
		unset($this->Tag);
		parent::tearDown();
	}

/**
 * testContainments method
 *
 * @return void
 */
	public function testContainments() {
		$r = $this->_containments($this->Event, array('Comment' => array('conditions' => array('Comment.user_id' => 2))));
		$this->assertTrue(Set::matches('/Event/keep/Comment/conditions[Comment.user_id=2]', $r));

		$r = $this->_containments($this->User, array(
			'EventFeatured' => array(
				'Featured' => array(
					'id',
					'Category' => 'name'
				)
		)));
		$this->assertEquals(array('id'), Hash::extract($r, 'EventFeatured.keep.Featured.fields'));

		$r = $this->_containments($this->Event, array(
			'Comment' => array(
				'User',
				'conditions' => array('Comment' => array('user_id' => 2)),
			),
		));
		$this->assertTrue(Set::matches('/User', $r));
		$this->assertTrue(Set::matches('/Comment', $r));
		$this->assertTrue(Set::matches('/Event/keep/Comment/conditions/Comment[user_id=2]', $r));

		$r = $this->_containments($this->Event, array('Comment(comment, published)' => 'Attachment(attachment)', 'User(user)'));
		$this->assertTrue(Set::matches('/Comment', $r));
		$this->assertTrue(Set::matches('/User', $r));
		$this->assertTrue(Set::matches('/Event/keep/Comment', $r));
		$this->assertTrue(Set::matches('/Event/keep/User', $r));
		$this->assertEquals(array('comment', 'published'), Hash::extract($r, 'Event.keep.Comment.fields'));
		$this->assertEquals(array('user'), Hash::extract($r, 'Event.keep.User.fields'));
		$this->assertTrue(Set::matches('/Comment/keep/Attachment', $r));
		$this->assertEquals(array('attachment'), Hash::extract($r, 'Comment.keep.Attachment.fields'));

		$r = $this->_containments($this->Event, array('Comment' => array('limit' => 1)));
		$this->assertEquals(array('Comment', 'Event'), array_keys($r));
		$result = Hash::extract($r, 'Comment[keep]');
		$this->assertEquals(array('keep' => array()), array_shift($result));
		$this->assertTrue(Set::matches('/Event/keep/Comment', $r));
		$result = Hash::extract($r, 'Event.keep');
		$this->assertEquals(array('limit' => 1), array_shift($result));

		$r = $this->_containments($this->Event, array('Comment.User'));
		$this->assertEquals(array('User', 'Comment', 'Event'), array_keys($r));

		$result = Hash::extract($r, 'User[keep]');
		$this->assertEquals(array('keep' => array()), array_shift($result));

		$result = Hash::extract($r, 'Comment[keep]');
		$this->assertEquals(array('keep' => array('User' => array())), array_shift($result));

		$result = Hash::extract($r, 'Event[keep]');
		$this->assertEquals(array('keep' => array('Comment' => array())), array_shift($result));

		$r = $this->_containments($this->Tag, array('Event' => array('User' => array('Comment' => array(
			'Attachment' => array('conditions' => array('Attachment.id >' => 1))
		)))));
		$this->assertTrue(Set::matches('/Attachment', $r));
		$this->assertTrue(Set::matches('/Comment/keep/Attachment/conditions', $r));
		$this->assertEquals(array('Attachment.id >' => 1), $r['Comment']['keep']['Attachment']['conditions']);
		$this->assertTrue(Set::matches('/User/keep/Comment', $r));
		$this->assertTrue(Set::matches('/Event/keep/User', $r));
		$this->assertTrue(Set::matches('/Tag/keep/Event', $r));
	}

/**
 * testInvalidContainments method
 *
 * @expectedException PHPUnit_Framework_Error
 * @return void
 */
	public function testInvalidContainments() {
		$this->_containments($this->Event, array('Comment', 'InvalidBinding'));
	}

/**
 * testInvalidContainments method with suppressing error notices
 *
 * @return void
 */
	public function testInvalidContainmentsNoNotices() {
		$this->Event->Behaviors->load('Containable', array('notices' => false));
		$this->_containments($this->Event, array('Comment', 'InvalidBinding'));
	}

/**
 * testBeforeFind method
 *
 * @return void
 */
	public function testBeforeFind() {
		$r = $this->Event->find('all', array('contain' => array('Comment')));
		$this->assertFalse(Set::matches('/User', $r));
		$this->assertTrue(Set::matches('/Comment', $r));
		$this->assertFalse(Set::matches('/Comment/User', $r));

		$r = $this->Event->find('all', array('contain' => 'Comment.User'));
		$this->assertTrue(Set::matches('/Comment/User', $r));
		$this->assertFalse(Set::matches('/Comment/Event', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment' => array('User', 'Event'))));
		$this->assertTrue(Set::matches('/Comment/User', $r));
		$this->assertTrue(Set::matches('/Comment/Event', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment' => array('conditions' => array('Comment.user_id' => 2)))));
		$this->assertFalse(Set::matches('/Comment[user_id!=2]', $r));
		$this->assertTrue(Set::matches('/Comment[user_id=2]', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment.user_id = 2')));
		$this->assertFalse(Set::matches('/Comment[user_id!=2]', $r));

		$r = $this->Event->find('all', array('contain' => 'Comment.id DESC'));
		$ids = $descIds = Hash::extract($r, 'Comment[1].id');
		rsort($descIds);
		$this->assertEquals($ids, $descIds);

		$r = $this->Event->find('all', array('contain' => 'Comment'));
		$this->assertTrue(Set::matches('/Comment[user_id!=2]', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment' => array('fields' => 'comment'))));
		$this->assertFalse(Set::matches('/Comment/created', $r));
		$this->assertTrue(Set::matches('/Comment/comment', $r));
		$this->assertFalse(Set::matches('/Comment/updated', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment' => array('fields' => array('comment', 'updated')))));
		$this->assertFalse(Set::matches('/Comment/created', $r));
		$this->assertTrue(Set::matches('/Comment/comment', $r));
		$this->assertTrue(Set::matches('/Comment/updated', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment' => array('comment', 'updated'))));
		$this->assertFalse(Set::matches('/Comment/created', $r));
		$this->assertTrue(Set::matches('/Comment/comment', $r));
		$this->assertTrue(Set::matches('/Comment/updated', $r));

		$r = $this->Event->find('all', array('contain' => array('Comment(comment,updated)')));
		$this->assertFalse(Set::matches('/Comment/created', $r));
		$this->assertTrue(Set::matches('/Comment/comment', $r));
		$this->assertTrue(Set::matches('/Comment/updated', $r));

		$r = $this->Event->find('all', array('contain' => 'Comment.created'));
		$this->assertTrue(Set::matches('/Comment/created', $r));
		$this->assertFalse(Set::matches('/Comment/comment', $r));

		$r = $this->Event->find('all', array('contain' => array('User.Event(title)', 'Comment(comment)')));
		$this->assertFalse(Set::matches('/Comment/Event', $r));
		$this->assertFalse(Set::matches('/Comment/User', $r));
		$this->assertTrue(Set::matches('/Comment/comment', $r));
		$this->assertFalse(Set::matches('/Comment/created', $r));
		$this->assertTrue(Set::matches('/User/Event/title', $r));
		$this->assertFalse(Set::matches('/User/Event/created', $r));

		$r = $this->Event->find('all', array('contain' => array()));
		$this->assertFalse(Set::matches('/User', $r));
		$this->assertFalse(Set::matches('/Comment', $r));
	}

/**
 * testBeforeFindWithNonExistingBinding method
 *
 * @expectedException PHPUnit_Framework_Error
 * @return void
 */
	public function testBeforeFindWithNonExistingBinding() {
		$this->Event->find('all', array('contain' => array('Comment' => 'NonExistingBinding')));
	}

/**
 * testContain method
 *
 * @return void
 */
	public function testContain() {
		$this->Event->contain('Comment.User');
		$r = $this->Event->find('all');
		$this->assertTrue(Set::matches('/Comment/User', $r));
		$this->assertFalse(Set::matches('/Comment/Event', $r));

		$r = $this->Event->find('all');
		$this->assertFalse(Set::matches('/Comment/User', $r));
	}

/**
 * testContainFindList method
 *
 * @return void
 */
	public function testContainFindList() {
		$this->Event->contain('Comment.User');
		$result = $this->Event->find('list');
		$expected = array(
			1 => 'First Event',
			2 => 'Second Event',
			3 => 'Third Event'
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('list', array('fields' => array('Event.id', 'User.id'), 'contain' => array('User')));
		$expected = array(
			1 => '1',
			2 => '3',
			3 => '1'
		);
		$this->assertEquals($expected, $result);
	}

/**
 * Test that mixing contain() and the contain find option.
 *
 * @return void
 */
	public function testContainAndContainOption() {
		$this->Event->contain();
		$r = $this->Event->find('all', array(
			'contain' => array('Comment')
		));
		$this->assertTrue(isset($r[0]['Comment']), 'No comment returned');
	}

/**
 * testFindEmbeddedNoBindings method
 *
 * @return void
 */
	public function testFindEmbeddedNoBindings() {
		$result = $this->Event->find('all', array('contain' => false));
		$expected = array(
			array('Event' => array(
				'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
				'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
			)),
			array('Event' => array(
				'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
				'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
			)),
			array('Event' => array(
				'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
				'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
			))
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindFirstLevel method
 *
 * @return void
 */
	public function testFindFirstLevel() {
		$this->Event->contain('User');
		$result = $this->Event->find('all', array('recursive' => 1));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				)
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain('User', 'Comment');
		$result = $this->Event->find('all', array('recursive' => 1));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Comment' => array(
					array(
						'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31'
					),
					array(
						'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31'
					),
					array(
						'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31'
					),
					array(
						'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
						'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'Comment' => array(
					array(
						'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31'
					),
					array(
						'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31'
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Comment' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindEmbeddedFirstLevel method
 *
 * @return void
 */
	public function testFindEmbeddedFirstLevel() {
		$result = $this->Event->find('all', array('contain' => array('User')));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				)
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array('contain' => array('User', 'Comment')));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Comment' => array(
					array(
						'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31'
					),
					array(
						'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31'
					),
					array(
						'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31'
					),
					array(
						'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
						'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'Comment' => array(
					array(
						'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31'
					),
					array(
						'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31'
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Comment' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindSecondLevel method
 *
 * @return void
 */
	public function testFindSecondLevel() {
		$this->Event->contain(array('Comment' => 'User'));
		$result = $this->Event->find('all', array('recursive' => 2));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'Comment' => array(
					array(
						'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
						'User' => array(
							'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
						)
					),
					array(
						'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
						'User' => array(
							'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
						)
					),
					array(
						'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
						'User' => array(
							'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
						)
					),
					array(
						'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
						'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
						'User' => array(
							'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'Comment' => array(
					array(
						'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
						'User' => array(
							'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
						)
					),
					array(
						'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
						'User' => array(
							'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'Comment' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User' => 'EventFeatured'));
		$result = $this->Event->find('all', array('recursive' => 2));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31',
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				)
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User' => array('id', 'EventFeatured')));
		$result = $this->Event->find('all', array('recursive' => 2));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1,
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3,
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1,
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				)
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User' => array('EventFeatured', 'Comment')));
		$result = $this->Event->find('all', array('recursive' => 2));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					),
					'Comment' => array(
						array(
							'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
							'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31'
						),
						array(
							'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
							'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
						),
						array(
							'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
							'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31',
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					),
					'Comment' => array()
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					),
					'Comment' => array(
						array(
							'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
							'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31'
						),
						array(
							'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
							'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
						),
						array(
							'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
							'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31'
						)
					)
				)
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User' => array('EventFeatured')), 'Tag', array('Comment' => 'Attachment'));
		$result = $this->Event->find('all', array('recursive' => 2));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				),
				'Comment' => array(
					array(
						'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
						'Attachment' => array()
					),
					array(
						'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
						'Attachment' => array()
					),
					array(
						'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
						'Attachment' => array()
					),
					array(
						'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
						'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
						'Attachment' => array()
					)
				),
				'Tag' => array(
					array('id' => 1, 'tag' => 'tag1', 'created' => '2007-03-18 12:22:23', 'updated' => '2007-03-18 12:24:31'),
					array('id' => 2, 'tag' => 'tag2', 'created' => '2007-03-18 12:24:23', 'updated' => '2007-03-18 12:26:31')
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31',
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					)
				),
				'Comment' => array(
					array(
						'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
						'Attachment' => array(
							'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
							'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
						)
					),
					array(
						'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
						'Attachment' => array()
					)
				),
				'Tag' => array(
					array('id' => 1, 'tag' => 'tag1', 'created' => '2007-03-18 12:22:23', 'updated' => '2007-03-18 12:24:31'),
					array('id' => 3, 'tag' => 'tag3', 'created' => '2007-03-18 12:26:23', 'updated' => '2007-03-18 12:28:31')
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				),
				'Comment' => array(),
				'Tag' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindEmbeddedSecondLevel method
 *
 * @return void
 */
	public function testFindEmbeddedSecondLevel() {
		$result = $this->Event->find('all', array('contain' => array('Comment' => 'User')));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'Comment' => array(
					array(
						'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
						'User' => array(
							'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
						)
					),
					array(
						'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
						'User' => array(
							'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
						)
					),
					array(
						'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
						'User' => array(
							'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
						)
					),
					array(
						'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
						'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
						'User' => array(
							'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'Comment' => array(
					array(
						'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
						'User' => array(
							'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
						)
					),
					array(
						'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
						'User' => array(
							'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
							'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'Comment' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array('contain' => array('User' => 'EventFeatured')));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31',
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				)
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array('contain' => array('User' => array('EventFeatured', 'Comment'))));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					),
					'Comment' => array(
						array(
							'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
							'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31'
						),
						array(
							'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
							'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
						),
						array(
							'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
							'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31'
						)
					)
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31',
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					),
					'Comment' => array()
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					),
					'Comment' => array(
						array(
							'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
							'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31'
						),
						array(
							'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
							'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
						),
						array(
							'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
							'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31'
						)
					)
				)
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array('contain' => array('User' => 'EventFeatured', 'Tag', 'Comment' => 'Attachment')));
		$expected = array(
			array(
				'Event' => array(
					'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				),
				'Comment' => array(
					array(
						'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
						'Attachment' => array()
					),
					array(
						'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
						'Attachment' => array()
					),
					array(
						'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
						'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
						'Attachment' => array()
					),
					array(
						'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
						'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
						'Attachment' => array()
					)
				),
				'Tag' => array(
					array('id' => 1, 'tag' => 'tag1', 'created' => '2007-03-18 12:22:23', 'updated' => '2007-03-18 12:24:31'),
					array('id' => 2, 'tag' => 'tag2', 'created' => '2007-03-18 12:24:23', 'updated' => '2007-03-18 12:26:31')
				)
			),
			array(
				'Event' => array(
					'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
				),
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31',
					'EventFeatured' => array(
						array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
						)
					)
				),
				'Comment' => array(
					array(
						'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
						'Attachment' => array(
							'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
							'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
						)
					),
					array(
						'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
						'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
						'Attachment' => array()
					)
				),
				'Tag' => array(
					array('id' => 1, 'tag' => 'tag1', 'created' => '2007-03-18 12:22:23', 'updated' => '2007-03-18 12:24:31'),
					array('id' => 3, 'tag' => 'tag3', 'created' => '2007-03-18 12:26:23', 'updated' => '2007-03-18 12:28:31')
				)
			),
			array(
				'Event' => array(
					'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
					'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
				),
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31',
					'EventFeatured' => array(
						array(
							'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
						),
						array(
							'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
							'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
						)
					)
				),
				'Comment' => array(),
				'Tag' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindThirdLevel method
 *
 * @return void
 */
	public function testFindThirdLevel() {
		$this->User->contain(array('EventFeatured' => array('Featured' => 'Category')));
		$result = $this->User->find('all', array('recursive' => 3));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->User->contain(array('EventFeatured' => array('Featured' => 'Category', 'Comment' => array('Event', 'Attachment'))));
		$result = $this->User->find('all', array('recursive' => 3));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->User->contain(array('EventFeatured' => array('Featured' => 'Category', 'Comment' => 'Attachment'), 'Event'));
		$result = $this->User->find('all', array('recursive' => 3));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Event' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'Event' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindEmbeddedThirdLevel method
 *
 * @return void
 */
	public function testFindEmbeddedThirdLevel() {
		$result = $this->User->find('all', array('contain' => array('EventFeatured' => array('Featured' => 'Category'))));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->User->find('all', array('contain' => array('EventFeatured' => array('Featured' => 'Category', 'Comment' => array('Event', 'Attachment')))));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->User->find('all', array('contain' => array('EventFeatured' => array('Featured' => 'Category', 'Comment' => 'Attachment'), 'Event')));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Event' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'Event' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testSettingsThirdLevel method
 *
 * @return void
 */
	public function testSettingsThirdLevel() {
		$result = $this->User->find('all', array('contain' => array('EventFeatured' => array('Featured' => array('Category' => array('id', 'name'))))));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'name' => 'Category 1'
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'name' => 'Category 1'
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$r = $this->User->find('all', array('contain' => array(
			'EventFeatured' => array(
				'id', 'title',
				'Featured' => array(
					'id', 'category_id',
					'Category' => array('id', 'name')
				)
			)
		)));

		$this->assertTrue(Set::matches('/User[id=1]', $r));
		$this->assertFalse(Set::matches('/Event', $r) || Set::matches('/Comment', $r));
		$this->assertTrue(Set::matches('/EventFeatured', $r));
		$this->assertFalse(Set::matches('/EventFeatured/User', $r) || Set::matches('/EventFeatured/Comment', $r) || Set::matches('/EventFeatured/Tag', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured', $r));
		$this->assertFalse(Set::matches('/EventFeatured/Featured/EventFeatured', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured/Category', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured[id=1]', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured[id=1]/Category[id=1]', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured[id=1]/Category[name=Category 1]', $r));

		$r = $this->User->find('all', array('contain' => array(
			'EventFeatured' => array(
				'title',
				'Featured' => array(
					'id',
					'Category' => 'name'
				)
			)
		)));

		$this->assertTrue(Set::matches('/User[id=1]', $r));
		$this->assertFalse(Set::matches('/Event', $r) || Set::matches('/Comment', $r));
		$this->assertTrue(Set::matches('/EventFeatured', $r));
		$this->assertFalse(Set::matches('/EventFeatured/User', $r) || Set::matches('/EventFeatured/Comment', $r) || Set::matches('/EventFeatured/Tag', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured', $r));
		$this->assertFalse(Set::matches('/EventFeatured/Featured/EventFeatured', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured/Category', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured[id=1]', $r));
		$this->assertTrue(Set::matches('/EventFeatured/Featured[id=1]/Category[name=Category 1]', $r));

		$result = $this->User->find('all', array('contain' => array(
			'EventFeatured' => array(
				'title',
				'Featured' => array(
					'category_id',
					'Category' => 'name'
				)
			)
		)));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'title' => 'First Event', 'id' => 1, 'user_id' => 1,
						'Featured' => array(
							'category_id' => 1, 'id' => 1,
							'Category' => array(
								'name' => 'Category 1'
							)
						)
					),
					array(
						'title' => 'Third Event', 'id' => 3, 'user_id' => 1,
						'Featured' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'title' => 'Second Event', 'id' => 2, 'user_id' => 3,
						'Featured' => array(
							'category_id' => 1, 'id' => 2,
							'Category' => array(
								'name' => 'Category 1'
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$orders = array(
			'title DESC', 'title DESC, published DESC',
			array('title' => 'DESC'), array('title' => 'DESC', 'published' => 'DESC'),
		);
		foreach ($orders as $order) {
			$result = $this->User->find('all', array('contain' => array(
				'EventFeatured' => array(
					'title', 'order' => $order,
					'Featured' => array(
						'category_id',
						'Category' => 'name'
					)
				)
			)));
			$expected = array(
				array(
					'User' => array(
						'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
						'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
					),
					'EventFeatured' => array(
						array(
							'title' => 'Third Event', 'id' => 3, 'user_id' => 1,
							'Featured' => array()
						),
						array(
							'title' => 'First Event', 'id' => 1, 'user_id' => 1,
							'Featured' => array(
								'category_id' => 1, 'id' => 1,
								'Category' => array(
									'name' => 'Category 1'
								)
							)
						)
					)
				),
				array(
					'User' => array(
						'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
						'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
					),
					'EventFeatured' => array()
				),
				array(
					'User' => array(
						'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
						'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
					),
					'EventFeatured' => array(
						array(
							'title' => 'Second Event', 'id' => 2, 'user_id' => 3,
							'Featured' => array(
								'category_id' => 1, 'id' => 2,
								'Category' => array(
									'name' => 'Category 1'
								)
							)
						)
					)
				),
				array(
					'User' => array(
						'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
						'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
					),
					'EventFeatured' => array()
				)
			);
			$this->assertEquals($expected, $result);
		}
	}

/**
 * testFindThirdLevelNonReset method
 *
 * @return void
 */
	public function testFindThirdLevelNonReset() {
		$this->User->contain(false, array('EventFeatured' => array('Featured' => 'Category')));
		$result = $this->User->find('all', array('recursive' => 3));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->User->resetBindings();

		$this->User->contain(false, array('EventFeatured' => array('Featured' => 'Category', 'Comment' => array('Event', 'Attachment'))));
		$result = $this->User->find('all', array('recursive' => 3));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->User->resetBindings();

		$this->User->contain(false, array('EventFeatured' => array('Featured' => 'Category', 'Comment' => 'Attachment'), 'Event'));
		$result = $this->User->find('all', array('recursive' => 3));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Event' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'Event' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testFindEmbeddedThirdLevelNonReset method
 *
 * @return void
 */
	public function testFindEmbeddedThirdLevelNonReset() {
		$result = $this->User->find('all', array('reset' => false, 'contain' => array('EventFeatured' => array('Featured' => 'Category'))));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->_assertBindings($this->User, array('hasMany' => array('EventFeatured')));
		$this->_assertBindings($this->User->EventFeatured, array('hasOne' => array('Featured')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('Category')));

		$this->User->resetBindings();

		$this->_assertBindings($this->User, array('hasMany' => array('Event', 'EventFeatured', 'Comment')));
		$this->_assertBindings($this->User->EventFeatured, array('belongsTo' => array('User'), 'hasOne' => array('Featured'), 'hasMany' => array('Comment'), 'hasAndBelongsToMany' => array('Tag')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('EventFeatured', 'Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('belongsTo' => array('Event', 'User'), 'hasOne' => array('Attachment')));

		$result = $this->User->find('all', array('reset' => false, 'contain' => array('EventFeatured' => array('Featured' => 'Category', 'Comment' => array('Event', 'Attachment')))));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->_assertBindings($this->User, array('hasMany' => array('EventFeatured')));
		$this->_assertBindings($this->User->EventFeatured, array('hasOne' => array('Featured'), 'hasMany' => array('Comment')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('belongsTo' => array('Event'), 'hasOne' => array('Attachment')));

		$this->User->resetBindings();
		$this->_assertBindings($this->User, array('hasMany' => array('Event', 'EventFeatured', 'Comment')));
		$this->_assertBindings($this->User->EventFeatured, array('belongsTo' => array('User'), 'hasOne' => array('Featured'), 'hasMany' => array('Comment'), 'hasAndBelongsToMany' => array('Tag')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('EventFeatured', 'Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('belongsTo' => array('Event', 'User'), 'hasOne' => array('Attachment')));

		$result = $this->User->find('all', array('contain' => array('EventFeatured' => array('Featured' => 'Category', 'Comment' => array('Event', 'Attachment')), false)));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Event' => array(
									'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
								),
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Event' => array(
									'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
									'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
								),
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->_assertBindings($this->User, array('hasMany' => array('EventFeatured')));
		$this->_assertBindings($this->User->EventFeatured, array('hasOne' => array('Featured'), 'hasMany' => array('Comment')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('belongsTo' => array('Event'), 'hasOne' => array('Attachment')));

		$this->User->resetBindings();
		$this->_assertBindings($this->User, array('hasMany' => array('Event', 'EventFeatured', 'Comment')));
		$this->_assertBindings($this->User->EventFeatured, array('belongsTo' => array('User'), 'hasOne' => array('Featured'), 'hasMany' => array('Comment'), 'hasAndBelongsToMany' => array('Tag')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('EventFeatured', 'Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('belongsTo' => array('Event', 'User'), 'hasOne' => array('Attachment')));

		$result = $this->User->find('all', array('reset' => false, 'contain' => array('EventFeatured' => array('Featured' => 'Category', 'Comment' => 'Attachment'), 'Event')));
		$expected = array(
			array(
				'User' => array(
					'id' => 1, 'user' => 'mariano', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:16:23', 'updated' => '2007-03-17 01:18:31'
				),
				'Event' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 1, 'user_id' => 1, 'title' => 'First Event', 'body' => 'First Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
						'Featured' => array(
							'id' => 1, 'article_featured_id' => 1, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 1, 'article_id' => 1, 'user_id' => 2, 'comment' => 'First Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:45:23', 'updated' => '2007-03-18 10:47:31',
								'Attachment' => array()
							),
							array(
								'id' => 2, 'article_id' => 1, 'user_id' => 4, 'comment' => 'Second Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:47:23', 'updated' => '2007-03-18 10:49:31',
								'Attachment' => array()
							),
							array(
								'id' => 3, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Third Comment for First Event',
								'published' => 'Y', 'created' => '2007-03-18 10:49:23', 'updated' => '2007-03-18 10:51:31',
								'Attachment' => array()
							),
							array(
								'id' => 4, 'article_id' => 1, 'user_id' => 1, 'comment' => 'Fourth Comment for First Event',
								'published' => 'N', 'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31',
								'Attachment' => array()
							)
						)
					),
					array(
						'id' => 3, 'user_id' => 1, 'title' => 'Third Event', 'body' => 'Third Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31',
						'Featured' => array(),
						'Comment' => array()
					)
				)
			),
			array(
				'User' => array(
					'id' => 2, 'user' => 'nate', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:18:23', 'updated' => '2007-03-17 01:20:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			),
			array(
				'User' => array(
					'id' => 3, 'user' => 'larry', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:20:23', 'updated' => '2007-03-17 01:22:31'
				),
				'Event' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'
					)
				),
				'EventFeatured' => array(
					array(
						'id' => 2, 'user_id' => 3, 'title' => 'Second Event', 'body' => 'Second Event Body',
						'published' => 'Y', 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31',
						'Featured' => array(
							'id' => 2, 'article_featured_id' => 2, 'category_id' => 1, 'published_date' => '2007-03-31 10:39:23',
							'end_date' => '2007-05-15 10:39:23', 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31',
							'Category' => array(
								'id' => 1, 'parent_id' => 0, 'name' => 'Category 1',
								'created' => '2007-03-18 15:30:23', 'updated' => '2007-03-18 15:32:31'
							)
						),
						'Comment' => array(
							array(
								'id' => 5, 'article_id' => 2, 'user_id' => 1, 'comment' => 'First Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:53:23', 'updated' => '2007-03-18 10:55:31',
								'Attachment' => array(
									'id' => 1, 'comment_id' => 5, 'attachment' => 'attachment.zip',
									'created' => '2007-03-18 10:51:23', 'updated' => '2007-03-18 10:53:31'
								)
							),
							array(
								'id' => 6, 'article_id' => 2, 'user_id' => 2, 'comment' => 'Second Comment for Second Event',
								'published' => 'Y', 'created' => '2007-03-18 10:55:23', 'updated' => '2007-03-18 10:57:31',
								'Attachment' => array()
							)
						)
					)
				)
			),
			array(
				'User' => array(
					'id' => 4, 'user' => 'garrett', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99',
					'created' => '2007-03-17 01:22:23', 'updated' => '2007-03-17 01:24:31'
				),
				'Event' => array(),
				'EventFeatured' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->_assertBindings($this->User, array('hasMany' => array('Event', 'EventFeatured')));
		$this->_assertBindings($this->User->Event);
		$this->_assertBindings($this->User->EventFeatured, array('hasOne' => array('Featured'), 'hasMany' => array('Comment')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('hasOne' => array('Attachment')));

		$this->User->resetBindings();
		$this->_assertBindings($this->User, array('hasMany' => array('Event', 'EventFeatured', 'Comment')));
		$this->_assertBindings($this->User->Event, array('belongsTo' => array('User'), 'hasMany' => array('Comment'), 'hasAndBelongsToMany' => array('Tag')));
		$this->_assertBindings($this->User->EventFeatured, array('belongsTo' => array('User'), 'hasOne' => array('Featured'), 'hasMany' => array('Comment'), 'hasAndBelongsToMany' => array('Tag')));
		$this->_assertBindings($this->User->EventFeatured->Featured, array('belongsTo' => array('EventFeatured', 'Category')));
		$this->_assertBindings($this->User->EventFeatured->Comment, array('belongsTo' => array('Event', 'User'), 'hasOne' => array('Attachment')));
	}

/**
 * testEmbeddedFindFields method
 *
 * @return void
 */
	public function testEmbeddedFindFields() {
		$result = $this->Event->find('all', array(
			'contain' => array('User(user)'),
			'fields' => array('title'),
			'order' => array('Event.id' => 'ASC')
		));
		$expected = array(
			array('Event' => array('title' => 'First Event'), 'User' => array('user' => 'mariano', 'id' => 1)),
			array('Event' => array('title' => 'Second Event'), 'User' => array('user' => 'larry', 'id' => 3)),
			array('Event' => array('title' => 'Third Event'), 'User' => array('user' => 'mariano', 'id' => 1)),
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array(
			'contain' => array('User(id, user)'),
			'fields' => array('title'),
			'order' => array('Event.id' => 'ASC')
		));
		$expected = array(
			array('Event' => array('title' => 'First Event'), 'User' => array('user' => 'mariano', 'id' => 1)),
			array('Event' => array('title' => 'Second Event'), 'User' => array('user' => 'larry', 'id' => 3)),
			array('Event' => array('title' => 'Third Event'), 'User' => array('user' => 'mariano', 'id' => 1)),
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array(
			'contain' => array(
				'Comment(comment, published)' => 'Attachment(attachment)', 'User(user)'
			),
			'fields' => array('title'),
			'order' => array('Event.id' => 'ASC')
		));
		if (!empty($result)) {
			foreach ($result as $i => $article) {
				foreach ($article['Comment'] as $j => $comment) {
					$result[$i]['Comment'][$j] = array_diff_key($comment, array('id' => true));
				}
			}
		}
		$expected = array(
			array(
				'Event' => array('title' => 'First Event', 'id' => 1),
				'User' => array('user' => 'mariano', 'id' => 1),
				'Comment' => array(
					array('comment' => 'First Comment for First Event', 'published' => 'Y', 'article_id' => 1, 'Attachment' => array()),
					array('comment' => 'Second Comment for First Event', 'published' => 'Y', 'article_id' => 1, 'Attachment' => array()),
					array('comment' => 'Third Comment for First Event', 'published' => 'Y', 'article_id' => 1, 'Attachment' => array()),
					array('comment' => 'Fourth Comment for First Event', 'published' => 'N', 'article_id' => 1, 'Attachment' => array()),
				)
			),
			array(
				'Event' => array('title' => 'Second Event', 'id' => 2),
				'User' => array('user' => 'larry', 'id' => 3),
				'Comment' => array(
					array('comment' => 'First Comment for Second Event', 'published' => 'Y', 'article_id' => 2, 'Attachment' => array(
						'attachment' => 'attachment.zip', 'id' => 1
					)),
					array('comment' => 'Second Comment for Second Event', 'published' => 'Y', 'article_id' => 2, 'Attachment' => array())
				)
			),
			array(
				'Event' => array('title' => 'Third Event', 'id' => 3),
				'User' => array('user' => 'mariano', 'id' => 1),
				'Comment' => array()
			),
		);
		$this->assertEquals($expected, $result);
	}

/**
 * test that hasOne and belongsTo fields act the same in a contain array.
 *
 * @return void
 */
	public function testHasOneFieldsInContain() {
		$this->Event->unbindModel(array(
			'hasMany' => array('Comment')
		), true);
		unset($this->Event->Comment);
		$this->Event->bindModel(array(
			'hasOne' => array('Comment')
		));

		$result = $this->Event->find('all', array(
			'fields' => array('title', 'body'),
			'contain' => array(
				'Comment' => array(
					'fields' => array('comment')
				),
				'User' => array(
					'fields' => array('user')
				)
			),
			'order' => 'Event.id ASC',
		));
		$this->assertTrue(isset($result[0]['Event']['title']), 'title missing %s');
		$this->assertTrue(isset($result[0]['Event']['body']), 'body missing %s');
		$this->assertTrue(isset($result[0]['Comment']['comment']), 'comment missing %s');
		$this->assertTrue(isset($result[0]['User']['user']), 'body missing %s');
		$this->assertFalse(isset($result[0]['Comment']['published']), 'published found %s');
		$this->assertFalse(isset($result[0]['User']['password']), 'password found %s');
	}

/**
 * testFindConditionalBinding method
 *
 * @return void
 */
	public function testFindConditionalBinding() {
		$this->Event->contain(array(
			'User(user)',
			'Tag' => array(
				'fields' => array('tag', 'created'),
				'conditions' => array('created >=' => '2007-03-18 12:24')
			)
		));
		$result = $this->Event->find('all', array(
			'fields' => array('title'),
			'order' => array('Event.id' => 'ASC')
		));
		$expected = array(
			array(
				'Event' => array('id' => 1, 'title' => 'First Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array(array('tag' => 'tag2', 'created' => '2007-03-18 12:24:23'))
			),
			array(
				'Event' => array('id' => 2, 'title' => 'Second Event'),
				'User' => array('id' => 3, 'user' => 'larry'),
				'Tag' => array(array('tag' => 'tag3', 'created' => '2007-03-18 12:26:23'))
			),
			array(
				'Event' => array('id' => 3, 'title' => 'Third Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User(id,user)', 'Tag' => array('fields' => array('tag', 'created'))));
		$result = $this->Event->find('all', array('fields' => array('title'), 'order' => array('Event.id' => 'ASC')));
		$expected = array(
			array(
				'Event' => array('id' => 1, 'title' => 'First Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array(
					array('tag' => 'tag1', 'created' => '2007-03-18 12:22:23'),
					array('tag' => 'tag2', 'created' => '2007-03-18 12:24:23')
				)
			),
			array(
				'Event' => array('id' => 2, 'title' => 'Second Event'),
				'User' => array('id' => 3, 'user' => 'larry'),
				'Tag' => array(
					array('tag' => 'tag1', 'created' => '2007-03-18 12:22:23'),
					array('tag' => 'tag3', 'created' => '2007-03-18 12:26:23')
				)
			),
			array(
				'Event' => array('id' => 3, 'title' => 'Third Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array(
			'fields' => array('title'),
			'contain' => array('User(id,user)', 'Tag' => array('fields' => array('tag', 'created'))),
			'order' => array('Event.id' => 'ASC')
		));
		$expected = array(
			array(
				'Event' => array('id' => 1, 'title' => 'First Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array(
					array('tag' => 'tag1', 'created' => '2007-03-18 12:22:23'),
					array('tag' => 'tag2', 'created' => '2007-03-18 12:24:23')
				)
			),
			array(
				'Event' => array('id' => 2, 'title' => 'Second Event'),
				'User' => array('id' => 3, 'user' => 'larry'),
				'Tag' => array(
					array('tag' => 'tag1', 'created' => '2007-03-18 12:22:23'),
					array('tag' => 'tag3', 'created' => '2007-03-18 12:26:23')
				)
			),
			array(
				'Event' => array('id' => 3, 'title' => 'Third Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array(
			'User(id,user)',
			'Tag' => array(
				'fields' => array('tag', 'created'),
				'conditions' => array('created >=' => '2007-03-18 12:24')
			)
		));
		$result = $this->Event->find('all', array('fields' => array('title'), 'order' => array('Event.id' => 'ASC')));
		$expected = array(
			array(
				'Event' => array('id' => 1, 'title' => 'First Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array(array('tag' => 'tag2', 'created' => '2007-03-18 12:24:23'))
			),
			array(
				'Event' => array('id' => 2, 'title' => 'Second Event'),
				'User' => array('id' => 3, 'user' => 'larry'),
				'Tag' => array(array('tag' => 'tag3', 'created' => '2007-03-18 12:26:23'))
			),
			array(
				'Event' => array('id' => 3, 'title' => 'Third Event'),
				'User' => array('id' => 1, 'user' => 'mariano'),
				'Tag' => array()
			)
		);
		$this->assertEquals($expected, $result);

		$this->assertTrue(empty($this->User->Event->hasAndBelongsToMany['Tag']['conditions']));

		$result = $this->User->find('all', array('contain' => array(
			'Event.Tag' => array('conditions' => array('created >=' => '2007-03-18 12:24'))
		)));

		$this->assertTrue(Set::matches('/User[id=1]', $result));
		$this->assertFalse(Set::matches('/Event[id=1]/Tag[id=1]', $result));
		$this->assertTrue(Set::matches('/Event[id=1]/Tag[id=2]', $result));
		$this->assertTrue(empty($this->User->Event->hasAndBelongsToMany['Tag']['conditions']));

		$this->assertTrue(empty($this->User->Event->hasAndBelongsToMany['Tag']['order']));

		$result = $this->User->find('all', array('contain' => array(
			'Event.Tag' => array('order' => 'created DESC')
		)));

		$this->assertTrue(Set::matches('/User[id=1]', $result));
		$this->assertTrue(Set::matches('/Event[id=1]/Tag[id=1]', $result));
		$this->assertTrue(Set::matches('/Event[id=1]/Tag[id=2]', $result));
		$this->assertTrue(empty($this->User->Event->hasAndBelongsToMany['Tag']['order']));
	}

/**
 * testOtherFinds method
 *
 * @return void
 */
	public function testOtherFinds() {
		$result = $this->Event->find('count');
		$expected = 3;
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('count', array('conditions' => array('Event.id >' => '1')));
		$expected = 2;
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('count', array('contain' => array()));
		$expected = 3;
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User(id,user)', 'Tag' => array('fields' => array('tag', 'created'), 'conditions' => array('created >=' => '2007-03-18 12:24'))));
		$result = $this->Event->find('first', array('fields' => array('title')));
		$expected = array(
			'Event' => array('id' => 1, 'title' => 'First Event'),
			'User' => array('id' => 1, 'user' => 'mariano'),
			'Tag' => array(array('tag' => 'tag2', 'created' => '2007-03-18 12:24:23'))
		);
		$this->assertEquals($expected, $result);

		$this->Event->contain(array('User(id,user)', 'Tag' => array('fields' => array('tag', 'created'))));
		$result = $this->Event->find('first', array('fields' => array('title')));
		$expected = array(
			'Event' => array('id' => 1, 'title' => 'First Event'),
			'User' => array('id' => 1, 'user' => 'mariano'),
			'Tag' => array(
				array('tag' => 'tag1', 'created' => '2007-03-18 12:22:23'),
				array('tag' => 'tag2', 'created' => '2007-03-18 12:24:23')
			)
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('first', array(
			'fields' => array('title'),
			'order' => 'Event.id DESC',
			'contain' => array('User(id,user)', 'Tag' => array('fields' => array('tag', 'created')))
		));
		$expected = array(
			'Event' => array('id' => 3, 'title' => 'Third Event'),
			'User' => array('id' => 1, 'user' => 'mariano'),
			'Tag' => array()
		);
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('list', array(
			'contain' => array('User(id,user)'),
			'fields' => array('Event.id', 'Event.title')
		));
		$expected = array(
			1 => 'First Event',
			2 => 'Second Event',
			3 => 'Third Event'
		);
		$this->assertEquals($expected, $result);
	}

/**
 * testOriginalAssociations method
 *
 * @return void
 */
	public function testOriginalAssociations() {
		$this->Event->Comment->Behaviors->load('Containable');

		$options = array(
			'conditions' => array(
				'Comment.published' => 'Y',
			),
			'contain' => 'User',
			'recursive' => 1
		);

		$firstResult = $this->Event->Comment->find('all', $options);

		$this->Event->Comment->find('all', array(
			'conditions' => array(
				'User.user' => 'mariano'
			),
			'fields' => array('User.password'),
			'contain' => array('User.password'),
		));

		$result = $this->Event->Comment->find('all', $options);
		$this->assertEquals($firstResult, $result);

		$this->Event->unbindModel(array('hasMany' => array('Comment'), 'belongsTo' => array('User'), 'hasAndBelongsToMany' => array('Tag')), false);
		$this->Event->bindModel(array('hasMany' => array('Comment'), 'belongsTo' => array('User')), false);

		$r = $this->Event->find('all', array('contain' => array('Comment(comment)', 'User(user)'), 'fields' => array('title')));
		$this->assertTrue(Set::matches('/Event[id=1]', $r));
		$this->assertTrue(Set::matches('/User[id=1]', $r));
		$this->assertTrue(Set::matches('/Comment[article_id=1]', $r));
		$this->assertFalse(Set::matches('/Comment[id=1]', $r));

		$r = $this->Event->find('all');
		$this->assertTrue(Set::matches('/Event[id=1]', $r));
		$this->assertTrue(Set::matches('/User[id=1]', $r));
		$this->assertTrue(Set::matches('/Comment[article_id=1]', $r));
		$this->assertTrue(Set::matches('/Comment[id=1]', $r));

		$this->Event->bindModel(array('hasAndBelongsToMany' => array('Tag')), false);

		$this->Event->contain(false, array('User(id,user)', 'Comment' => array('fields' => array('comment'), 'conditions' => array('created >=' => '2007-03-18 10:49'))));
		$result = $this->Event->find('all', array('fields' => array('title'), 'limit' => 1, 'page' => 1, 'order' => 'Event.id ASC'));
		$expected = array(array(
			'Event' => array('id' => 1, 'title' => 'First Event'),
			'User' => array('id' => 1, 'user' => 'mariano'),
			'Comment' => array(
				array('comment' => 'Third Comment for First Event', 'article_id' => 1),
				array('comment' => 'Fourth Comment for First Event', 'article_id' => 1)
			)
		));
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array('fields' => array('title', 'User.id', 'User.user'), 'limit' => 1, 'page' => 2, 'order' => 'Event.id ASC'));
		$expected = array(array(
			'Event' => array('id' => 2, 'title' => 'Second Event'),
			'User' => array('id' => 3, 'user' => 'larry'),
			'Comment' => array(
				array('comment' => 'First Comment for Second Event', 'article_id' => 2),
				array('comment' => 'Second Comment for Second Event', 'article_id' => 2)
			)
		));
		$this->assertEquals($expected, $result);

		$result = $this->Event->find('all', array('fields' => array('title', 'User.id', 'User.user'), 'limit' => 1, 'page' => 3, 'order' => 'Event.id ASC'));
		$expected = array(array(
			'Event' => array('id' => 3, 'title' => 'Third Event'),
			'User' => array('id' => 1, 'user' => 'mariano'),
			'Comment' => array()
		));
		$this->assertEquals($expected, $result);

		$this->Event->contain(false, array('User' => array('fields' => 'user'), 'Comment'));
		$result = $this->Event->find('all');
		$this->assertTrue(Set::matches('/Event[id=1]', $result));
		$this->assertTrue(Set::matches('/User[user=mariano]', $result));
		$this->assertTrue(Set::matches('/Comment[article_id=1]', $result));
		$this->Event->resetBindings();

		$this->Event->contain(false, array('User' => array('fields' => array('user')), 'Comment'));
		$result = $this->Event->find('all');
		$this->assertTrue(Set::matches('/Event[id=1]', $result));
		$this->assertTrue(Set::matches('/User[user=mariano]', $result));
		$this->assertTrue(Set::matches('/Comment[article_id=1]', $result));
		$this->Event->resetBindings();
	}

/**
 * testResetAddedAssociation method
 *
 * @return void
 */
	public function testResetAddedAssociation() {
		$this->assertTrue(empty($this->Event->hasMany['EventsTag']));

		$this->Event->bindModel(array(
			'hasMany' => array('EventsTag')
		));
		$this->assertTrue(!empty($this->Event->hasMany['EventsTag']));

		$result = $this->Event->find('first', array(
			'conditions' => array('Event.id' => 1),
			'contain' => array('EventsTag')
		));

		$expected = array('Event', 'EventsTag');
		$this->assertTrue(!empty($result));
		$this->assertEquals('First Event', $result['Event']['title']);
		$this->assertTrue(!empty($result['EventsTag']));
		$this->assertEquals($expected, array_keys($result));

		$this->assertTrue(empty($this->Event->hasMany['EventsTag']));

		$this->JoinA = ClassRegistry::init('JoinA');
		$this->JoinB = ClassRegistry::init('JoinB');
		$this->JoinC = ClassRegistry::init('JoinC');

		$this->JoinA->Behaviors->load('Containable');
		$this->JoinB->Behaviors->load('Containable');
		$this->JoinC->Behaviors->load('Containable');

		$this->JoinA->JoinB->find('all', array('contain' => array('JoinA')));
		$this->JoinA->bindModel(array('hasOne' => array('JoinAsJoinC' => array('joinTable' => 'as_cs'))), false);
		$result = $this->JoinA->hasOne;
		$this->JoinA->find('all');
		$resultAfter = $this->JoinA->hasOne;
		$this->assertEquals($result, $resultAfter);
	}

/**
 * testResetAssociation method
 *
 * @return void
 */
	public function testResetAssociation() {
		$this->Event->Behaviors->load('Containable');
		$this->Event->Comment->Behaviors->load('Containable');
		$this->Event->User->Behaviors->load('Containable');

		$initialOptions = array(
			'conditions' => array(
				'Comment.published' => 'Y',
			),
			'contain' => 'User',
			'recursive' => 1,
		);

		$initialModels = $this->Event->Comment->find('all', $initialOptions);

		$findOptions = array(
			'conditions' => array(
				'User.user' => 'mariano',
			),
			'fields' => array('User.password'),
			'contain' => array('User.password')
		);
		$result = $this->Event->Comment->find('all', $findOptions);
		$result = $this->Event->Comment->find('all', $initialOptions);
		$this->assertEquals($initialModels, $result);
	}

/**
 * testResetDeeperHasOneAssociations method
 *
 * @return void
 */
	public function testResetDeeperHasOneAssociations() {
		$this->Event->User->unbindModel(array(
			'hasMany' => array('EventFeatured', 'Comment')
		), false);
		$userHasOne = array('hasOne' => array('EventFeatured', 'Comment'));

		$this->Event->User->bindModel($userHasOne, false);
		$expected = $this->Event->User->hasOne;
		$this->Event->find('all');
		$this->assertEquals($expected, $this->Event->User->hasOne);

		$this->Event->User->bindModel($userHasOne, false);
		$expected = $this->Event->User->hasOne;
		$this->Event->find('all', array(
			'contain' => array(
				'User' => array('EventFeatured', 'Comment')
			)
		));
		$this->assertEquals($expected, $this->Event->User->hasOne);

		$this->Event->User->bindModel($userHasOne, false);
		$expected = $this->Event->User->hasOne;
		$this->Event->find('all', array(
			'contain' => array(
				'User' => array(
					'EventFeatured',
					'Comment' => array('fields' => array('created'))
				)
			)
		));
		$this->assertEquals($expected, $this->Event->User->hasOne);

		$this->Event->User->bindModel($userHasOne, false);
		$expected = $this->Event->User->hasOne;
		$this->Event->find('all', array(
			'contain' => array(
				'User' => array(
					'Comment' => array('fields' => array('created'))
				)
			)
		));
		$this->assertEquals($expected, $this->Event->User->hasOne);

		$this->Event->User->bindModel($userHasOne, false);
		$expected = $this->Event->User->hasOne;
		$this->Event->find('all', array(
			'contain' => array(
				'User.EventFeatured' => array(
					'conditions' => array('EventFeatured.published' => 'Y')
				),
				'User.Comment'
			)
		));
		$this->assertEquals($expected, $this->Event->User->hasOne);
	}

/**
 * testResetMultipleHabtmAssociations method
 *
 * @return void
 */
	public function testResetMultipleHabtmAssociations() {
		$articleHabtm = array(
			'hasAndBelongsToMany' => array(
				'Tag' => array(
					'className' => 'Tag',
					'joinTable' => 'articles_tags',
					'foreignKey' => 'article_id',
					'associationForeignKey' => 'tag_id'
				),
				'ShortTag' => array(
					'className' => 'Tag',
					'joinTable' => 'articles_tags',
					'foreignKey' => 'article_id',
					'associationForeignKey' => 'tag_id',
					// LENGTH function mysql-only, using LIKE does almost the same
					'conditions' => "ShortTag.tag LIKE '???'"
				)
			)
		);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all');
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => 'Tag.tag'));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => 'Tag'));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => array('Tag' => array('fields' => array(null)))));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => array('Tag' => array('fields' => array('Tag.tag')))));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => array('Tag' => array('fields' => array('Tag.tag', 'Tag.created')))));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => 'ShortTag.tag'));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => 'ShortTag'));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => array('ShortTag' => array('fields' => array(null)))));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => array('ShortTag' => array('fields' => array('ShortTag.tag')))));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);

		$this->Event->resetBindings();
		$this->Event->bindModel($articleHabtm, false);
		$expected = $this->Event->hasAndBelongsToMany;
		$this->Event->find('all', array('contain' => array('ShortTag' => array('fields' => array('ShortTag.tag', 'ShortTag.created')))));
		$this->assertEquals($expected, $this->Event->hasAndBelongsToMany);
	}

/**
 * test that bindModel and unbindModel work with find() calls in between.
 *
 * @return void
 */
	public function testBindMultipleTimesWithFind() {
		$binding = array(
			'hasOne' => array(
				'EventsTag' => array(
					'foreignKey' => false,
					'type' => 'INNER',
					'conditions' => array(
						'EventsTag.article_id = Event.id'
					)
				),
				'Tag' => array(
					'type' => 'INNER',
					'foreignKey' => false,
					'conditions' => array(
						'EventsTag.tag_id = Tag.id'
					)
				)
			)
		);
		$this->Event->unbindModel(array('hasAndBelongsToMany' => array('Tag')));
		$this->Event->bindModel($binding);
		$result = $this->Event->find('all', array('limit' => 1, 'contain' => array('EventsTag', 'Tag')));

		$this->Event->unbindModel(array('hasAndBelongsToMany' => array('Tag')));
		$this->Event->bindModel($binding);
		$result = $this->Event->find('all', array('limit' => 1, 'contain' => array('EventsTag', 'Tag')));

		$associated = $this->Event->getAssociated();
		$this->assertEquals('hasAndBelongsToMany', $associated['Tag']);
		$this->assertFalse(isset($associated['EventTag']));
	}

/**
 * test that autoFields doesn't splice in fields from other databases.
 *
 * @return void
 */
	public function testAutoFieldsWithMultipleDatabases() {
		$config = new DATABASE_CONFIG();

		$this->skipIf(
			!isset($config->test) || !isset($config->test2),
			'Primary and secondary test databases not configured, ' .
			'skipping cross-database join tests. ' .
			' To run these tests, you must define $test and $test2 ' .
			'in your database configuration.'
		);

		$db = ConnectionManager::getDataSource('test2');
		$this->fixtureManager->loadSingle('User', $db);

		$this->Event->User->setDataSource('test2');

		$result = $this->Event->find('all', array(
			'fields' => array('Event.title'),
			'contain' => array('User')
		));
		$this->assertTrue(isset($result[0]['Event']));
		$this->assertTrue(isset($result[0]['User']));
	}

/**
 * test that autoFields doesn't splice in columns that aren't part of the join.
 *
 * @return void
 */
	public function testAutoFieldsWithRecursiveNegativeOne() {
		$this->Event->recursive = -1;
		$result = $this->Event->field('title', array('Event.title' => 'First Event'));
		$this->assertNoErrors();
		$this->assertEquals('First Event', $result, 'Field is wrong');
	}

/**
 * test that find(all) doesn't return incorrect values when mixed with containable.
 *
 * @return void
 */
	public function testFindAllReturn() {
		$result = $this->Event->find('all', array(
			'conditions' => array('Event.id' => 999999999)
		));
		$this->assertEmpty($result, 'Should be empty.');
	}

/**
 * testLazyLoad method
 *
 * @return void
 */
	public function testLazyLoad() {
		// Local set up
		$this->User = ClassRegistry::init('User');
		$this->User->bindModel(array(
			'hasMany' => array('Event', 'EventFeatured', 'Comment')
		), false);

		try {
			$this->User->find('first', array(
				'contain' => 'Comment',
				'lazyLoad' => true
			));
		} catch (Exception $e) {
			$exceptions = true;
		}
		$this->assertTrue(empty($exceptions));
	}

/**
 * _containments method
 *
 * @param Model $Model
 * @param array $contain
 * @return void
 */
	protected function _containments($Model, $contain = array()) {
		if (!is_array($Model)) {
			$result = $Model->containments($contain);
			return $this->_containments($result['models']);
		}
		$result = $Model;
		foreach ($result as $i => $containment) {
			$result[$i] = array_diff_key($containment, array('instance' => true));
		}
		return $result;
	}

/**
 * _assertBindings method
 *
 * @param Model $Model
 * @param array $expected
 * @return void
 */
	protected function _assertBindings(Model $Model, $expected = array()) {
		$expected = array_merge(array(
			'belongsTo' => array(),
			'hasOne' => array(),
			'hasMany' => array(),
			'hasAndBelongsToMany' => array()
		), $expected);
		foreach ($expected as $binding => $expect) {
			$this->assertEquals(array_keys($Model->$binding), $expect);
		}
	}
}
