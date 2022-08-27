# Можно использовать как симфониевские объекты валидаций так и слова из ларавель валидацию внутри симфони форм
    
    $form = FormFactory::create(FormType::class, $user)
        ->add('name', TextType::class)
        ->add('email', EmailType::class, [
            'rules' => 'unique:users,email',
        ])
        ->add('save', SubmitType::class, ['label' => 'Save user']);
