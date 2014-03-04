<?php

class ContactController extends Controller 
{
    protected function beforeAction($action) 
    {
        if (parent::beforeAction($action)) {
            // Добавление CSS файла для пользователей.
        }
        return true;
    }

    //User block
    public function actionIndex() 
    {
        if(Yii::app()->user->checkAccess('readContact')) {
            $criteria = new CDbCriteria();
            $sort = new CSort();
            $sort->sortVar = 'sort';
            // сортировка по умолчанию 
            $sort->defaultOrder = 'surname ASC';
            $dataProvider = new CActiveDataProvider('Contact', 
                array(
                    'criteria'=>$criteria,
                    'sort'=>$sort,
                    'pagination'=>array(
                        'pageSize'=>'13'
                    )
                )
            );
            if ($id_item = Yii::app()->user->getFlash('saved_id')) {
                $model = Contact::model()->findByPk($id_item);
                //$group = UserGroup::getUserGroupArray();
                //$view = $this->renderPartial('user/edituser', array('model'=>$model, 'group'=>$group), true, true);
                $view = $this->renderPartial('editcontact', array('model'=>$model), true, true);
            }
            $this->render('contacts', array('data'=>$dataProvider, 'view'=>$view));
        } else {
            throw new CHttpException(403,Yii::t('yii','У Вас недостаточно прав доступа.'));
        }
    }

    public function actionCreateContact()
    {
        if(Yii::app()->user->checkAccess('createContact')) {
            $model = new Contact;
            if(isset($_POST['Contact'])) {
                $model->attributes = $_POST['Contact'];
                 if($model->save()){
                    $message = 'Создан контакт ' . $model->name . ' ' . $model->surname;
                    Changes::saveChange($message);

                    Yii::app()->user->setFlash('saved_id', $model->id);
                    Yii::app()->user->setFlash('message', 'Контакт '.$model->login.' создан успешно.');
                    $this->redirect('/admin/contact/');
                }
                print_r($model->getErrors());
            }
            $this->renderPartial('editcontact', array('model'=>$model), false, true);
        } else {
            throw new CHttpException(403,Yii::t('yii','У Вас недостаточно прав доступа.'));
        }
    }

    public function actionEditContact($id) 
    {
        $model = Contact::model()->findByPk($id);   
        if (Yii::app()->user->checkAccess('editContact')) {
            if (isset($_POST['Contact'])) {
                $changes = array();
                foreach ($_POST['Contact'] as $key => $value) {
                    if (trim($model[$key]) != trim($value)) {
                        $changes[$key]['before'] = $model[$key];
                        $changes[$key]['after'] = $value;
                        $model[$key] = trim($value);
                    }
                }
                
                $model->attributes = $_POST['Contact'];
                if (!empty($changes)) {
                    $message = 'У контактного лица с id = ' . $id . ' были изменены слудующие поля: ';
                    $k = 0;
                    foreach ($changes as $key => $value) {
                        $k++;
                        $message .= $k . ') Поле ' . $key . ' c ' . $changes[$key]['before'] . ' на ' . $changes[$key]['after'] . '; ';
                    }
                    Changes::saveChange($message);
                }
                
                if ($model->save()) {
                    Yii::app()->user->setFlash('saved_id', $model->id);
                    Yii::app()->user->setFlash('message', 'Контактное лицо ' . $model->login . ' сохранено успешно.');
                    $this->redirect('/admin/contact/');
                }
            }
            //$this->renderPartial('editcontact', array('model' => $model, 'status' => UserController::$userStatus), false, true); // ?????
            $this->renderPartial('editcontact', array('model' => $model), false, true); // ?????
        } else {
            throw new CHttpException(403, Yii::t('yii', 'У Вас недостаточно прав доступа.'));
        }
    }

    public function actionDeleteContact($id) 
    {
        $model = User::model()->findByPk($id);
        if (Yii::app()->user->checkAccess('deleteContact')) {
            if (User::model()->deleteByPk($id)) {
                $message = 'Удален контакт ' . $model['name'] . ' ' . $model['surname'];
                Changes::saveChange($message);
                Yii::app()->user->setFlash('message', 'Контакт удален успешно.');
                $this->redirect('/admin/contact/');
            }
        } else {
            throw new CHttpException(403, Yii::t('yii', 'У Вас недостаточно прав доступа.'));
        }
    }
    
    public function getCompanies() 
    {
        return $allCompanies = Yii::app()->db->createCommand()
            ->select('id, company')
            ->from('user')
            ->order('company')
            ->queryAll()
        ;
    }
}
