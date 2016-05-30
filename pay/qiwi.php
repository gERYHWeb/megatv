<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оплата");

CModule::IncludeModule("sale");
global $USER;

if($USER->IsAuthorized())
{
    $dbAccountCurrency = CSaleUserAccount::GetList(
        array(),
        array("USER_ID" => $USER->GetID()),
        false,
        false,
        array("CURRENT_BUDGET")
    );
    $arAccount = $dbAccountCurrency->Fetch();
    
    $budget = $arAccount["CURRENT_BUDGET"];
    $orderID = IntVal($_POST["orderNumber"]);    // Код заказа
    
    // Внесем (снимем) деньги на счет
    if (CSaleUserAccount::UpdateAccount(
            $USER->GetID(),
            floatval($_POST["Sum"]),
            "RUB",
            "Внесение средств на счет",
            $orderID))
        {
        ?>
        <p>На ваш счет внесены средства в размере <?=floatval($_POST["Sum"])?> руб.<br />
        <a href="/personal/">Перейти в личный кабинет</a>.</p>
        <?
        LocalRedirect("/personal/services/?pay-status=success&sum=".floatval($_POST["Sum"]));
    }
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>