<?php

/**
 * This is the model class for table "contact".
 *
 * The followings are the available columns in table 'contact':
 * @property integer $id
 * @property integer $u_id
 * @property string $login
 * @property string $password
 * @property string $name
 * @property string $surname
 * @property string $secondname
 * @property string $email
 * @property integer $status
 * @property integer $phone
 * @property integer $phone2
 *
 * The followings are the available model relations:
 * @property User $u
 */
class UserContact extends CActiveRecord
{
        public $confirm_password;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user_contact';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('u_id, status, phone, phone2', 'numerical', 'integerOnly'=>true),
			array('login', 'length', 'max'=>64),
			array('password, name, surname, secondname, email', 'safe'),
                        array('login, name, secondname, surname, phone, email', 'required'),
                        array('name, secondname, surname', 'match', 'pattern'=>'/^[\S]*$/', 'message'=>'Поле "{attribute}" не должно содержать пробелы'),
                        array('password', 'length', 'min'=>6, 'allowEmpty'=>false),
                        array('password','match', 'pattern'=>'/^([a-zA-Zа-яА-ЯёЁ\d]+)$/i', 'message'=>'Пароль должен содержать только следующие символы: 0-9 a-z A-Z а-я А-Я'),
                        array('password', 'match', 'pattern'=>'/([a-zA-Zа-яА-Я]+)/', 'message'=>'Пароль должен содержать минимум одну букву'),
                        array('password', 'match', 'pattern'=>'/([0-9]+)/', 'message'=>'Пароль должен содержать минимум одну цифру'),
                        array('confirm_password', 'compare', 'compareAttribute'=>'password', 'message'=>'Пароли не совпадают'),

			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, u_id, login, password, name, surname, secondname, email, status, phone, phone2', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'u' => array(self::BELONGS_TO, 'User', 'u_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'u_id' => 'Фирма',
			'login' => 'Логин',
			'password' => 'Пароль',
                        'confirm_password' => 'Подтверждение пароля',
			'name' => 'Имя',
			'surname' => 'Фамилия',
			'secondname' => 'Отчество',
			'email' => 'Email',
			'status' => 'Статус',
			'phone' => 'Телефон',
			'phone2' => 'Телефон №2',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('u_id',$this->u_id);
		$criteria->compare('login',$this->login,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('surname',$this->surname,true);
		$criteria->compare('secondname',$this->secondname,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('phone',$this->phone);
		$criteria->compare('phone2',$this->phone2);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Contact the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}