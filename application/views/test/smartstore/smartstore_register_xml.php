<soap:Envelope
        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:shop="http://shopn.platform.nhncorp.com/">
    <soap:Body>
        <shop:ManageProductRequest>

            <shop:RequestID>test</shop:RequestID>

            <shop:AccessCredentials>
                <shop:AccessLicense><?=$accessLicense?></shop:AccessLicense>
                <shop:Timestamp><?=$timestamp?></shop:Timestamp>
                <shop:Signature><?=$signature?></shop:Signature>
            </shop:AccessCredentials>

            <shop:Version><?=$version?></shop:Version>

            <SellerId><?=$seller_id?></SellerId>

            <Product>

                <? if($productId){ ?>
                    <shop:ProductId><?=$productId?></shop:ProductId>
                <?}?>

                <shop:StatusType>SALE</shop:StatusType>

                <shop:SaleType>NEW</shop:SaleType>

                <shop:CategoryId><?=$categoryId?></shop:CategoryId>

                <shop:SellerManagementCode><?=$itemId?></shop:SellerManagementCode>

                <shop:Name><?=$data['item']['it_name']?></shop:Name>

                <? if($confirm_category == 'KC' && $kc_confirmation == 'N'){ ?>
                    <shop:KCCertifiedProductExclusion><?=$kc_type_exclusion?></shop:KCCertifiedProductExclusion>
                    <shop:KCExemptionType><?=$kc_type?></shop:KCExemptionType>
                <?}else if($kc_confirmation == 'Y'){?>
                    <shop:KCCertifiedProductExclusion><?=$kc_type_exclusion?></shop:KCCertifiedProductExclusion>
                    <shop:KCExemptionType><?=$kc_type?></shop:KCExemptionType>

                <?}?>

                <shop:OriginArea>
                    <shop:Code><?=$original_area_code?></shop:Code>
                    <shop:Importer><?=$importer?></shop:Importer>
                </shop:OriginArea>

                <shop:TaxType><?=$tax_type?></shop:TaxType>
                <shop:MinorPurchasable>Y</shop:MinorPurchasable>
                <shop:Image>
                    <shop:Representative>
                        <shop:URL><?=$arr_img['uploaded_img'][0]?></shop:URL>
                    </shop:Representative>
                    <shop:OptionalList>
                        <? foreach($arr_img['uploaded_img'] as $value){ ?>
                            <shop:Optional>
                                <shop:URL><?=$value?></shop:URL>
                            </shop:Optional>
                        <?}?>
                    </shop:OptionalList>
                </shop:Image>
                <shop:DetailContent><![CDATA[<?=$data['item']['sizeHTML']?>]]></shop:DetailContent>
                <shop:AfterServiceTelephoneNumber>test</shop:AfterServiceTelephoneNumber>
                <shop:AfterServiceGuideContent><?=$AfterServiceGuideContent?></shop:AfterServiceGuideContent>
                <shop:SalePrice><?=$data['item']['it_scrap_price']?></shop:SalePrice>
                <shop:StockQuantity>999</shop:StockQuantity>

                <? if(!empty($discountAmount)){?>
                    <shop:SellerDiscount>
                        <shop:Amount><?=$discountAmount?></shop:Amount>
                        <shop:MobileAmount><?=$discountAmount?></shop:MobileAmount>
                    </shop:SellerDiscount>
                <?}?>

                <? if(!empty($arr_attribute_values)){?>
                    <shop:ProductAttributeList>
                        <? for ($i = 0 ; $i < count($arr_attribute_values['AttributeSeq']) ; $i++){ ?>
                        <shop:ProductAttribute>
                            <shop:AttributeSeq><?=$arr_attribute_values['AttributeSeq'][$i]?></shop:AttributeSeq>
                            <shop:AttributeValueSeq><?=$arr_attribute_values['AttributeValueSeq'][$i]?></shop:AttributeValueSeq>
                        </shop:ProductAttribute>
                        <?}?>
                    </shop:ProductAttributeList>
                <?}?>

                <shop:Model>
                    <shop:BrandName><?=$data['itemSpec']['values']['it_brand']?></shop:BrandName>
                </shop:Model>

                <shop:Delivery>

                    <shop:Type>1</shop:Type>

                    <shop:BundleGroupAvailable>N</shop:BundleGroupAvailable>

                    <shop:FeeType><?=$FeeType?></shop:FeeType>

                    <shop:BaseFee><?=$BaseFee?></shop:BaseFee>

                    <shop:ReturnDeliveryCompanyPriority>0</shop:ReturnDeliveryCompanyPriority>

                    <shop:ReturnFee><?=$ReturnFee?></shop:ReturnFee>

                    <shop:ExchangeFee><?=$ExchangeFee?></shop:ExchangeFee>

                    <shop:ShippingAddressId><?=$ShippingAddressId?></shop:ShippingAddressId>

                    <shop:ReturnAddressId><?=$ReturnAddressId?></shop:ReturnAddressId>

                    <shop:PayType>2</shop:PayType>

                    <shop:AreaType>2</shop:AreaType>

                    <shop:Area2ExtraFee>6000</shop:Area2ExtraFee>

                    <? if(!empty($ExpectedDeliveryPeriod)){ ?>
                        <shop:CustomProductAfterOrderYn>Y</shop:CustomProductAfterOrderYn>
                        <shop:ExpectedDeliveryPeriodType><?=$ExpectedDeliveryPeriod?></shop:ExpectedDeliveryPeriodType>
                    <?}else{?>
                        <shop:CustomProductAfterOrderYn>N</shop:CustomProductAfterOrderYn>
                    <?}?>

                </shop:Delivery>

                <? if(!empty($Point)){ ?>
                    <shop:Mileage>
                        <shop:Amount><?=$Point?></shop:Amount>
                    </shop:Mileage>
                <?}?>

                <? if(!empty($eventMsg)){ ?>
                    <shop:PublicityPhraseContent><?=$eventMsg?></shop:PublicityPhraseContent>
                <?}?>

                <shop:ItselfProductionProductYn>N</shop:ItselfProductionProductYn>

                <shop:Display>Y</shop:Display>

                <shop:ProductSummary>
                    <shop:Wear>
                        <shop:NoRefundReason><?=$img_reference?></shop:NoRefundReason>
                        <shop:ReturnCostReason><?=$img_reference?></shop:ReturnCostReason>
                        <shop:QualityAssuranceStandard><?=$img_reference?></shop:QualityAssuranceStandard>
                        <shop:CompensationProcedure><?=$img_reference?></shop:CompensationProcedure>
                        <shop:TroubleShootingContents><?=$img_reference?></shop:TroubleShootingContents>
                        <shop:Material><?=$img_reference?></shop:Material>
                        <shop:Color><?=$img_reference?></shop:Color>
                        <shop:Size><?=$img_reference?></shop:Size>
                        <shop:Manufacturer><?=$img_reference?></shop:Manufacturer>
                        <shop:Caution><?=$img_reference?></shop:Caution>
                        <shop:PackDate><?=$PackDate?></shop:PackDate>
                        <shop:WarrantyPolicy><?=$img_reference?></shop:WarrantyPolicy>
                        <shop:AfterServiceDirector><?=$img_reference?></shop:AfterServiceDirector>
                    </shop:Wear>
                </shop:ProductSummary>

                <shop:BrandCertificationYn>N</shop:BrandCertificationYn>

            </Product>
        </shop:ManageProductRequest>
    </soap:Body>
</soap:Envelope>