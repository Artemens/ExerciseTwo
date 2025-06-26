<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="form-wrapper">
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
        <form action="<?= POST_FORM_ACTION_URI ?>" method="POST" enctype="multipart/form-data" id="web-form">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="WEB_FORM_ID" value="<?= $arParams['WEB_FORM_ID'] ?>">

            <div class="form-header">
                <div class="form-header-left">
                    <h3>Связаться</h3>
                </div>
                <div class="form-header-right">
                    <p>Наши сотрудники помогут выполнить подбор услуги и расчет цены с учетом ваших требований</p>
                </div>
            </div>

            <div class="form-layout">
                <div class="form-left-column">
                    <?php 
                    $questions = array_filter($arResult['QUESTIONS'], function($question) {
                        return $question['STRUCTURE'][0]['FIELD_TYPE'] != 'hidden';
                    });
                    $questions = array_values($questions);
                    $requiredFields = [];
                    ?>
                    
                    <?php for ($i = 0; $i < min(4, count($questions)); $i += 2): ?>
                        <div class="form-row">
                            <?php for ($j = $i; $j < min($i + 2, 4, count($questions)); $j++): ?>
                                <?php 
                                $question = $questions[$j];
                                $fieldName = 'form_'.$question['STRUCTURE'][0]['FIELD_TYPE'].'_'.$question['STRUCTURE'][0]['ID'];
                                if ($question['REQUIRED'] == 'Y') {
                                    $requiredFields[] = $fieldName;
                                }
                                ?>
                                <div class="form-field form-text-field">
                                    <label for="<?= $fieldName ?>">
                                        <?= $question['CAPTION'] ?>
                                        <?php if ($question['REQUIRED'] == 'Y'): ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?= str_replace(
                                        'id="'.$fieldName.'"', 
                                        'id="'.$fieldName.'" class="form-input"', 
                                        $question['HTML_CODE']
                                    ) ?>
                                    <div class="form-error" id="<?= $fieldName ?>-error" style="display: none;">
                                        <span class="error-icon">▲</span> Заполните это поле
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div class="form-right-column">
                    <?php 
                    $textQuestion = null;
                    foreach ($arResult['QUESTIONS'] as $question) {
                        if ($question['STRUCTURE'][0]['FIELD_TYPE'] == 'textarea') {
                            $textQuestion = $question;
                            break;
                        }
                    }
                    ?>
                    
                    <?php if ($textQuestion): ?>
                        <?php 
                        $fieldName = 'form_'.$textQuestion['STRUCTURE'][0]['FIELD_TYPE'].'_'.$textQuestion['STRUCTURE'][0]['ID'];
                        ?>
                        <div class="form-field large-text-field">
                            <label for="<?= $fieldName ?>">
                                <?= $textQuestion['CAPTION'] ?>
                                <?php if ($textQuestion['REQUIRED'] == 'Y'): ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <?= str_replace(
                                'id="'.$fieldName.'"', 
                                'id="'.$fieldName.'" class="form-textarea"', 
                                $textQuestion['HTML_CODE']
                            ) ?>
                            <?php if ($textQuestion['REQUIRED'] == 'Y'): ?>
                                <div class="form-error" id="<?= $fieldName ?>-error" style="display: none;">
                                    <span class="error-icon">▲</span> Заполните это поле
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php 
            foreach ($arResult['QUESTIONS'] as $fieldSid => $question) {
                if ($question['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden') {
                    echo '<input type="hidden" name="form_'.$question['STRUCTURE'][0]['FIELD_TYPE'].'_'.$question['STRUCTURE'][0]['ID'].'" value="'.$question['HTML_VALUE'].'">';
                }
            }
            ?>

            <div class="form-footer">
                <div class="form-footer-text">
                    <p>Нажимая «Отправить», Вы подтверждаете, что ознакомлены, полностью согласны и принимаете условия «Согласия на обработку персональных данных».</p>
                </div>
                <div class="form-submit">
                    <input type="submit" name="web_form_submit" value="<?= $arResult['arForm']['BUTTON'] ?>" id="submit-btn">
                </div>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('web-form');
            const submitBtn = document.getElementById('submit-btn');
            const requiredFields = <?= json_encode($requiredFields) ?>;
            
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(String(email).toLowerCase());
            }
            
            function validateForm() {
                let isValid = true;
                
                requiredFields.forEach(fieldName => {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    const errorElement = document.getElementById(`${fieldName}-error`);
                    
                    if (!input.value.trim()) {
                        errorElement.style.display = 'block';
                        isValid = false;
                    } else {
                        errorElement.style.display = 'none';
                        
                        if (fieldName.includes('email') && !validateEmail(input.value)) {
                            errorElement.textContent = '▲ Введите корректный email';
                            errorElement.style.display = 'block';
                            isValid = false;
                        }
                    }
                });
                
                return isValid;
            }

            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    
                    const firstError = document.querySelector('.form-error[style="display: block;"]');
                    if (firstError) {
                        firstError.scrollIntoView({ 
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
                        const errorElement = document.getElementById(`${fieldName}-error`);
                        errorElement.style.display = 'none';
                    });
                }
            });
        });
        </script>
    <?php endif; ?>
</div>