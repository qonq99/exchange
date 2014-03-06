<?php
class SiteController extends Controller
{
    public function actionIndex($s = null)
    {
        $this->forward('/transport/i/');
    }

    public function actionDescription($id)
    {
	$transportInfo=Yii::app()->db->createCommand("SELECT * from transport where id='".$id."'")->queryRow();
        $allRatesForTransport = Yii::app()->db->createCommand()
            ->select('r.date, r.price, u.name')
            ->from('rate r')
            ->join('user u', 'r.user_id=u.id')
            ->where('r.transport_id=:id', array(':id'=>$id))
            ->order('r.date desc')
            ->queryAll()
        ;
        
        $this->render('item', array('rateData' => $dataProvider, 'transportInfo' => $transportInfo));
    }
    
    public function actionFeedback()
    {
        $this->render('feedback');
    }
    
    public function actionHelp()
    {
        $this->render('help');
    }
    
    public function actionRegistration()
    { 
        $model = new RegistrationForm;

        if(isset($_POST['RegistrationForm'])) {
            $record = User::model()->find(array(
                'condition'=>'inn=:inn',
                'params'=>array(':inn'=>$_POST['inn']))
            );

            if($record === null) { //new user
                //$this->password = crypt($_POST['User_password'], User::model()->blowfishSalt());
                // Save in database
                $userInfo = array();

                $user = new User();
                $user->attributes = $_POST['RegistrationForm'];
                //var_dump($user->attributes);
                $user->status = 0; //User::USER_NOT_CONFIRMED;
                $user->company = $_POST['RegistrationForm']['ownership'] . ' "' . $_POST['RegistrationForm']['company'] . '"';
                
                $password = $this->randomPassword();
                $user->password = crypt($password, User::model()->blowfishSalt(16));
                
                $user->login = $user->inn;
                if($user->save()){
                    $newFerrymanFields = new UserField;
                    $newFerrymanFields->user_id = $user->id;
                    $newFerrymanFields->mail_transport_create_1 = false;
                    $newFerrymanFields->mail_transport_create_2 = false;
                    $newFerrymanFields->mail_kill_rate = false;
                    $newFerrymanFields->mail_before_deadline = false;
                    $newFerrymanFields->mail_deadline = true;
                    $newFerrymanFields->with_nds = (bool)$_POST['RegistrationForm']['nds'];            
                    if(!$newFerrymanFields->save()) {
                        var_dump($newFerrymanFields->getErrors()); 
                        exit;
                        
                    };
                
                
                    $this->sendMail(Yii::app()->params['adminEmail'], 1, $_POST['RegistrationForm']);
                    $this->sendMail($_POST['email'], 0, $_POST['RegistrationForm']);

                    Dialog::message('flash-success', 'Отправлено!', 'Ваша заявка отправлена. Спасибо за интерес, проявленный к нашей компании.');
                    $this->redirect('/user/login/');
                }
            }
        } else {
            $this->render('registration', array('model' => $model));
        }
    }
    
    /*public function actionRestore()
    { 
        $model = new RestoreForm;
        //var_dump($_POST['RestoreForm']['inn']);exit;
        if(isset($_POST['RestoreForm'])) {
            $inn = $_POST['RestoreForm']['inn'];
            $user = User::model()->find(array(
                'condition'=>'inn=:inn',
                'params'=>array(':inn'=>$inn))
            );
            
            if($user) {
                if($user->email) {
                    //var_dump('has mail');exit;
                    $password = 111111; //$this->randomPassword();
                    $user->password = crypt($password, User::model()->blowfishSalt());
                    if($user->save()) {
                        Dialog::message('flash-success', 'Внимание!', 'На ваш почтовый ящик были высланы инструкции для смены пароля.');
                        $this->redirect('/user/login/');
                    } else var_dump($user->getErrors());
                    
                    //Dialog::message('flash-success', 'Внимание!', 'На ваш почтовый ящик были высланы инструкции для смены пароля.');

                    // отправить письмо самому
                    $email = new TEmail;
                    $email->from_email = Yii::app()->params['adminEmail'];
                    $email->from_name  = 'Биржа перевозок ЛБР АгроМаркет';
                    $email->to_email   = $user->email;
                    $email->to_name    = '';
                    $email->subject    = 'Смена пароля';
                    $email->type = 'text/html';
                    $email->body = '<div>'.
                            '<p>Ваш пароль для "Онлайн биржи перевозок ЛБР-АгроМаркет" был изменен:</p>'.
                            '<p>Новый пароль: <b>'.$password.'</b></p>'.
                            '<p>Для смены пароля зайдите в свой аккаунт и воспользуйтесь вкладкой "Настроки->Смена пароля"</p>'.
                        '</div>
                        <hr/><h5>Это уведомление является автоматическим, на него не следует отвечать.</h5>
                    ';
                    //$email->sendMail();
                    //Dialog::message('flash-success', 'Отправлено!', 'Ваша заявка отправлена. Спасибо за интерес, проявленный к нашей компании.');
                    $this->redirect('/user/login/');

                } else {
                    Dialog::message('flash-success', 'Внимание!', 'Ваша заявка на восстановление доступа отправлена, в ближайшее время с вами свяжутся представители нашей компании.');
                
                    $email = new TEmail;
                    $email->from_email = Yii::app()->params['adminEmail'];
                    $email->from_name  = 'Биржа перевозок ЛБР АгроМаркет';
                    $email->to_email   = Yii::app()->params['logistEmail'];
                    $email->to_name    = '';
                    $email->subject    = 'Смена пароля';
                    $email->type = 'text/html';
                    $email->body = '<div>'.
                            '<p>Перевозчик "'. $user->company. '" ИНН/УНП = '. $user->inn .' запросил восстановление доступов, однако он не указал email. </p>'.
                            '<p>Контактный телефон: '. $user->phone . '</p>'.
                        '</div>
                        <hr/><h5>Это уведомление является автоматическим, на него не следует отвечать.</h5>
                    ';
                    //$email->sendMail();
                    // отправить письмо логисту
                }
            } else {
                Dialog::message('flash-success', 'Внимание!', 'Пользователя с таким "ИНН/УНП" не найдено, свяжитесь с отделом логистики.');
            }
            //$this->redirect('/user/login/');
            
        } else {
            $this->render('restore', array('model' => $model));
        }
    }*/
     
    public function sendMail($to, $typeMessage, $post)
    {
        $email = new TEmail;
        $email->from_email = Yii::app()->params['adminEmail'];
        $email->from_name  = 'Биржа перевозок ЛБР АгроМаркет';
        $email->to_email   = $to;
        $email->to_name    = '';
        $email->subject    = 'Заявка на регистрацию';
        $email->type = 'text/html';
        if(!empty($typeMessage)) {
            $description = (!empty($post['description'])) ? '<p>Примечание:<b>'.$post['description'].'</b></p>' : '' ;
            $email->body = '
              <div>
                  <p>Компания "'.$post['firmName'].'" подала заявку на регистрацию в бирже перевозок ЛБР АгроМаркет.</p>
                  <p>Контактное лицо: <b>'.$post['name']. ' ' .$post['surname'].'</b></p>
                  <p>Телефон: <b>'.$post['phone'].'</b></p>
                  <p>Email: <b>'.$post['email'].'</b></p>'.
                   $description .
              '</div>
              <hr/><h5>Это уведомление является автоматическим, на него не следует отвечать.</h5>
            ';
        } else {
            $email->body = '
                <div> 
                    <p>Ваша регистрация будет рассмотрена и Вам будут высланы инструкции с дальнейшими действиями. </p>
                </div>
                <hr/><h5>Это уведомление является автоматическим, на него не следует отвечать.</h5>
            ';
        }
        $email->sendMail();
    }
     
    public function actionQuick() 
    { 
        $model = new QuickForm;
        $model->attributes = $_POST['QuickForm'];
        if($model->validate()) {
            $user = User::model()->findByPk($model->user); 
            $email = new TEmail;
            $email->from_email = $user->email;
            $email->from_name  = 'Биржа перевозок ЛБР АгроМаркет';
            $email->to_email   = Yii::app()->params['adminEmail'];
            $email->to_name    = 'Модератору';
            $email->subject    = '';
            $email->type = 'text/html';
            
            $email->body = "<div>
                    <p>
                      Пользоваетель " . $user->name." (" . $user->email . ") 
                      находясь в перевозке с id = ".$model->transport." обратился к модератору Биржи перевозок ЛБР 'АгроМаркет'
                      со следующим обращением:
                    </p>
                    <p>" . $model->message . "</p>
                </div>
            ";
            $email->sendMail();
        }
        
        Dialog::message('flash-success', 'Отправлено!', 'Спасибо, '.$user->name.'! Ваше письмо отправлено!');
        $this->redirect(array('transport/description/id/1'));
    }
    
    private function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 16; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}