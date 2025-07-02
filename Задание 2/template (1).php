<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="contact-form">
    <?php if ($arResult['isFormErrors'] == 'Y'): ?>
        <div class="form-errors">
            <?= $arResult['FORM_ERRORS_TEXT'] ?>
        </div>
    <?php endif; ?>

    <?php if ($arResult['isFormNote'] == 'Y'): ?>
        <div class="form-success">
            <?= $arResult['FORM_NOTE'] ?>
        </div>
    <?php else: ?>
        <form class="contact-form__form" action="<?= POST_FORM_ACTION_URI ?>" method="POST" enctype="multipart/form-data">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="WEB_FORM_ID" value="<?= $arParams['WEB_FORM_ID'] ?>">

            <div class="contact-form__head">
                <div class="contact-form__head-title">Связаться</div>
                <div class="contact-form__head-text">Наши сотрудники помогут выполнить подбор услуги и&nbsp;расчет цены с&nbsp;учетом ваших требований</div>
            </div>

            <div class="contact-form__form-inputs">
                <?php 
                $inputQuestions = array_filter($arResult['QUESTIONS'], function($question) {
                    return $question['STRUCTURE'][0]['FIELD_TYPE'] != 'hidden' && 
                           $question['STRUCTURE'][0]['FIELD_TYPE'] != 'textarea';
                });
                
                $requiredFields = [];
                ?>
                
                <?php foreach ($inputQuestions as $question): ?>
                    <?php 
                    $fieldName = 'form_'.$question['STRUCTURE'][0]['FIELD_TYPE'].'_'.$question['STRUCTURE'][0]['ID'];
                    if ($question['REQUIRED'] == 'Y') {
                        $requiredFields[] = $fieldName;
                    }
                    ?>
                    <div class="input contact-form__input">
                        <label class="input__label" for="<?= $fieldName ?>">
                            <div class="input__label-text">
                                <?= $question['CAPTION'] ?>
                                <?php if ($question['REQUIRED'] == 'Y'): ?>*<?php endif; ?>
                            </div>
                            <?= $question['HTML_CODE'] ?>
                            <div class="input__notification">
                                <?php if (strpos($question['HTML_CODE'], 'type="email"') !== false): ?>
                                    Неверный формат почты
                                <?php elseif ($question['REQUIRED'] == 'Y'): ?>
                                    Поле должно содержать не менее 3-х символов
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php 
            $textareaQuestion = null;
            foreach ($arResult['QUESTIONS'] as $question) {
                if ($question['STRUCTURE'][0]['FIELD_TYPE'] == 'textarea') {
                    $textareaQuestion = $question;
                    break;
                }
            }
            ?>
            
            <?php if ($textareaQuestion): ?>
                <?php 
                $fieldName = 'form_textarea_'.$textareaQuestion['STRUCTURE'][0]['ID'];
                if ($textareaQuestion['REQUIRED'] == 'Y') {
                    $requiredFields[] = $fieldName;
                }
                ?>
                <div class="contact-form__form-message">
                    <div class="input">
                        <label class="input__label" for="<?= $fieldName ?>">
                            <div class="input__label-text">
                                <?= $textareaQuestion['CAPTION'] ?>
                                <?php if ($textareaQuestion['REQUIRED'] == 'Y'): ?>*<?php endif; ?>
                            </div>
                            <?= $textareaQuestion['HTML_CODE'] ?>
                            <div class="input__notification"></div>
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <?php 
            foreach ($arResult['QUESTIONS'] as $question) {
                if ($question['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden') {
                    echo $question['HTML_CODE'];
                }
            }
            ?>

            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных данных&raquo;.</div>
                <button class="form-button contact-form__bottom-button" type="submit" name="web_form_submit" value="Y" data-success="Отправлено" data-error="Ошибка отправки">
                    <div class="form-button__title"><?= $arResult['arForm']['BUTTON'] ?></div>
                </button>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.contact-form__form');
            const requiredFields = <?= json_encode($requiredFields) ?>;
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(String(email).toLowerCase());
            }
            
            function validateForm() {
                let isValid = true;
                
                requiredFields.forEach(fieldName => {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    const notification = input ? input.closest('.input__label').querySelector('.input__notification') : null;
                    
                    if (!input || !notification) return;
                    
                    notification.style.display = 'none';
                    
                    if (!input.value.trim()) {
                        notification.style.display = 'block';
                        isValid = false;
                    } else if (input.type === 'email' && !validateEmail(input.value)) {
                        notification.textContent = 'Неверный формат почты';
                        notification.style.display = 'block';
                        isValid = false;
                    } else if (input.value.trim().length < 3) {
                        notification.textContent = 'Поле должно содержать не менее 3-х символов';
                        notification.style.display = 'block';
                        isValid = false;
                    }
                });
                
                return isValid;
            }

            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    
                    const firstError = form.querySelector('.input__notification[style="display: block;"]');
                    if (firstError) {
                        firstError.closest('.input').scrollIntoView({ 
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            });

            requiredFields.forEach(fieldName => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (input) {
                    input.addEventListener('input', function() {
                        const notification = this.closest('.input__label').querySelector('.input__notification');
                        if (notification) {
                            notification.style.display = 'none';
                        }
                    });
                }
            });
        });
        </script>
    <?php endif; ?>
</div>