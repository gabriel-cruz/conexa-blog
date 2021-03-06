<?php

/**
 * This is the model class for table "tbl_post".
 *
 * The followings are the available columns in table 'tbl_post':
 * @property integer $post_id
 * @property string $title
 * @property string $content
 * @property integer $tags
 * @property string $author
 * @property integer $date
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Comment[] $comments
 * @property Users $author0
 * @property Tags $tags0
 */
class Post extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Post the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'tbl_post';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, content, author, tags', 'required'),
			array('title', 'length', 'max'=>128),
			array('tags', 'match', 'pattern'=>'/^[\w\s,]+$/',
			'message'=>'Tags só podem conter palavras.'),
			array('tags', 'normalizeTags'),

			array('title', 'safe', 'on'=>'search'), 
		);
	}

	public function normalizeTags($attribute,$params){
    	$this->tags=Tags::array2string(array_unique(Tags::string2array($this->tags)));
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'comments'=> array(self::HAS_MANY, 'Comment', 'post', 'order'=>'comments.comn_date DESC'),
			'commentCount'=> array(self::STAT, 'Comment', 'post'),	
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'post_id' => 'Postagem',
			'title' => 'Título',
			'content' => 'Conteúdo',
			'tags' => 'Categoria',
			'author'=> 'Nome',
			'date' => 'Data',
			'update_time' => 'Update Time',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('post_id',$this->post_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('tags',$this->tags);
		$criteria->compare('author',$this->author);
		$criteria->compare('date',$this->date);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function getUrl(){
		return Yii::app()->createUrl('post/view', array(
			'post_id'=>$this->post_id,
			'title'=>$this->title,
		));
	}

	/*protected function beforeSave(){

		if(parent::beforeSave()){

			if($this->isNewRecord){
				$this->date=$this->update_time=time();
				$this->author_id=Yii::app()->users->;
			}
			else{
				$this->update_time=time();
			}

			return true;
		}
		else{
			return false;
		}
	}*/

	protected function afterSave(){
		parent::afterSave();
		Tags::model()->updateFrequency($this->_oldTags, $this->tags);
	}

	private $_oldTags;

	protected function afterFind(){
		parent::afterFind();
		$this->_oldTags=$this->tags;
	}

	public function addComment($comment){
		$comment->post=$this->post_id;

		return $comment->save();
	}
}