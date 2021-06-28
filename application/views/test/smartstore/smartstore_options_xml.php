<soap:Envelope
        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:tns="http://shopn.platform.nhncorp.com/"
>
    <soap:Body>
        <tns:ManageOptionRequest>
            <tns:AccessCredentials>
                <tns:AccessLicense><?=$accessLicense?></tns:AccessLicense>
                <tns:Timestamp><?=$timestamp?></tns:Timestamp>
                <tns:Signature><?=$signature?></tns:Signature>
            </tns:AccessCredentials>
            <tns:Version><?=$version?></tns:Version>
            <SellerId><?=$sellerId?></SellerId>
            <tns:Option>
                <tns:ProductId><?=$productId?></tns:ProductId>
                <tns:SortType>ABC</tns:SortType>
                <tns:Combination>
                    <?
                    $arr_option_subject = explode(';',$option_subject);
                    $arr_option_subject[0] = isset($arr_option_subject[0])?$arr_option_subject[0]:"";
                    $arr_option_subject[1] = isset($arr_option_subject[1])?$arr_option_subject[1]:"";
                    $arr_option_subject = array_filter($arr_option_subject);
                    ?>

                    <? if(count($arr_option_subject) == 1){ ?>

                        <tns:Names>
                                <tns:Name1><?=$arr_option_subject[0]?></tns:Name1>
                        </tns:Names>
                        <tns:ItemList>
                            <? foreach($optionInfo as $value){?>
                                <tns:Item>
                                    <tns:Value1><?=$value['prop']?></tns:Value1>
                                    <tns:Price><?=($value['io_price']-$original_price)?></tns:Price>
                                    <tns:Quantity><?=$value['io_stock_qty']?></tns:Quantity>
                                    <tns:Usable>Y</tns:Usable>
                                </tns:Item>
                            <?}?>
                        </tns:ItemList>

                    <?}else{?>

                        <tns:Names>
                            <? if(mb_strlen($arr_option_subject[0]) == 2){ ?>
                                <tns:Name1><?=$arr_option_subject[1]?></tns:Name1>
                                <tns:Name2><?=$arr_option_subject[0]?></tns:Name2>
                            <?}else{?>
                                <tns:Name1><?=$arr_option_subject[0]?></tns:Name1>
                                <tns:Name2><?=$arr_option_subject[1]?></tns:Name2>
                            <?}?>
                        </tns:Names>
                        <tns:ItemList>
                            <? foreach($optionInfo as $value){
                                $arr_option = explode(';',$value['prop']);
                                $arr_option[0] = isset($arr_option[0])?$arr_option[0]:"";
                                $arr_option[1] = isset($arr_option[1])?$arr_option[1]:"";
                                ?>
                                <tns:Item>
                                    <? if(mb_strlen($arr_option_subject[0]) == 2){ ?>
                                        <tns:Value1><?=$arr_option[1]?></tns:Value1>
                                        <tns:Value2><?=$arr_option[0]?></tns:Value2>
                                    <?}else{?>
                                        <tns:Value1><?=$arr_option[0]?></tns:Value1>
                                        <tns:Value2><?=$arr_option[1]?></tns:Value2>
                                    <?}?>
                                    <tns:Price><?=($value['io_price']-$original_price)?></tns:Price>
                                    <tns:Quantity><?=$value['io_stock_qty']?></tns:Quantity>
                                    <tns:Usable>Y</tns:Usable>
                                </tns:Item>
                            <?}?>
                        </tns:ItemList>

                    <?}?>


                </tns:Combination>
            </tns:Option>
        </tns:ManageOptionRequest>
    </soap:Body>

</soap:Envelope>