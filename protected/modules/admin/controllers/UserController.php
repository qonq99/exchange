<?php

class UserController extends Controller {
    /* @const */

    private static $userStatus = array(
        User::USER_NOT_CONFIRMED => 'Не подтвежден',
        User::USER_ACTIVE => 'Активен',
        User::USER_WARNING => 'Предупрежден',
        User::USER_TEMPORARY_BLOCKED => 'Временно заблокирован',
        User::USER_BLOCKED => 'Заблокирован',
    );

    protected function beforeAction($action) {
        if (parent::beforeAction($action)) {
            // Добавление CSS файла для пользователей.
        }
        return true;
    }

    //User block
    public function actionIndex() 
    {
        if(Yii::app()->user->checkAccess('readUser'))
            {
                $criteria = new CDbCriteria();
                $sort = new CSort();
                $sort->sortVar = 'sort';
                // сортировка по умолчанию 
                $sort->defaultOrder = 'surname ASC';
                $dataProvider = new CActiveDataProvider('User', 
                        array(
                            'criteria'=>$criteria,
                            'sort'=>$sort,
                            'pagination'=>array(
                                'pageSize'=>'13'
                            )
                        )
                );
                if ($id_item = Yii::app()->user->getFlash('saved_id')){
                    $model = User::model()->findByPk($id_item);
                    //$group = UserGroup::getUserGroupArray();
                    //$view = $this->renderPartial('user/edituser', array('model'=>$model, 'group'=>$group), true, true);
                    $view = $this->renderPartial('user/edituser', array('model'=>$model), true, true);
                }
                $this->render('user/users', array('data'=>$dataProvider, 'view'=>$view));
            }else{
                throw new CHttpException(403,Yii::t('yii','У Вас недостаточно прав доступа.'));
            }
    }

    public function actionCreateUser() 
    {
        if(Yii::app()->user->checkAccess('createUser'))
        {
            $model = new User();
            $model->status=1;
           // $group = UserGroup::getUserGroupArray();
            if (isset($_POST['User'])){
                $model->attributes = $_POST['User'];
                if($model->save()){
                    $message = 'Создан пользователь ' . $model->name . ' ' . $model->surname;
                    Changes::saveChange($message);
                    
                    $newFerrymanFields = new UserField;
                    $newFerrymanFields->user_id = $model->id;
                    $newFerrymanFields->mail_transport_create_1 = false;
                    $newFerrymanFields->mail_transport_create_2 = false;
                    $newFerrymanFields->mail_kill_rate = false;
                    $newFerrymanFields->mail_before_deadline = false;
                    $newFerrymanFields->mail_deadline = true;
                    $newFerrymanFields->site_transport_create_1 = true;
                    $newFerrymanFields->site_transport_create_2 = true;
                    $newFerrymanFields->site_kill_rate = true;
                    $newFerrymanFields->site_deadline = true;
                    $newFerrymanFields->site_before_deadline = true;            
                    $newFerrymanFields->with_nds = false;            
                    $newFerrymanFields->save();
                    
                    Yii::app()->user->setFlash('saved_id', $model->id);
                    Yii::app()->user->setFlash('message', 'Пользователь '.$model->login.' создан успешно.');
                    $this->redirect('/admin/user/');
                }
            }
            $this->renderPartial('user/edituser', array('model'=>$model, 'group'=>$group), false, true);
            //$this->renderPartial('user/edituser', array('model'=>$model), false, true);
        }else{
            throw new CHttpException(403,Yii::t('yii','У Вас недостаточно прав доступа.'));
        }
    }

    public function actionEditUser($id) 
    {
       
        $model = User::model()->findByPk($id);
        
        if (Yii::app()->user->checkAccess('editUser')) {
            
            if (isset($_POST['User'])) {
                $changes = array();
                 //echo 112;exit;
                foreach ($_POST['User'] as $key => $value) {
                    if (trim($model[$key]) != trim($value)) {
                        $changes[$key]['before'] = $model[$key];
                        $changes[$key]['after'] = $value;
                        $model[$key] = trim($value);
                    }
                }
                if (!empty($changes)) {
                    $message = 'У пользователя с id = ' . $id . ' были изменены слудующие поля: ';
                    $k = 0;
                    foreach ($changes as $key => $value) {
                        $k++;
                        $message .= $k . ') Поле ' . $key . ' c ' . $changes[$key]['before'] . ' на ' . $changes[$key]['after'] . '; ';
                    }
                    Changes::saveChange($message);
                }
                //$model->attributes = $_POST['User'];
                if ($model->save()) {
                    Yii::app()->user->setFlash('saved_id', $model->id);
                    Yii::app()->user->setFlash('message', 'Пользователь ' . $model->login . ' сохранен успешно.');
                    $this->redirect('/admin/user/');
                }
            }
            $this->renderPartial('user/edituser', array('model' => $model, 'status' => self::$userStatus), false, true);
        } else {
            throw new CHttpException(403, Yii::t('yii', 'У Вас недостаточно прав доступа.'));
        }
    }

    public function actionDeleteUser($id) {
        $model = User::model()->findByPk($id);
        
        if (Yii::app()->user->checkAccess('deleteUser') && $id != Yii::app()->user->_id) {
            $message = 'Удален пользователь ' . $model['name'] . ' ' . $model['surname'];
            if (User::model()->deleteByPk($id)) {
                Changes::saveChange($message);
                Yii::app()->user->setFlash('message', 'Пользователь удален успешно.');
                $this->redirect('/admin/user/');
            }
        } else {
            throw new CHttpException(403, Yii::t('yii', 'У Вас недостаточно прав доступа.'));
        }
    }
}
