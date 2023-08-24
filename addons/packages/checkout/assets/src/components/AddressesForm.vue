<script setup lang="ts">
import {reactive, ref, toRaw, onMounted} from "vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";

defineProps({
    email: String
});

const emit = defineEmits<{
    (e: 'AddressDataSubmitted', formData: object): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const formData = reactive({
    country: '',
    address: '',
    postcode: '',
    city: '',
    state: '',
    notes: ''
});

onMounted(() => {
    const shippingData = checkoutDataStore.shippingData;
    if(shippingData.country !== ''){
        formData.country = shippingData.country;
    }
    if(shippingData.address !== ''){
        formData.address = shippingData.address;
    }
    if(shippingData.postcode !== ''){
        formData.postcode = shippingData.postcode;
    }
    if(shippingData.city !== ''){
        formData.city = shippingData.city;
    }
    if(shippingData.state !== ''){
        formData.state = shippingData.state;
    }
});

function confirmFormData(){
    emit('AddressDataSubmitted', toRaw(formData));
}
</script>

<template>
    <section class="woocommerce-checkout-steps__content" id="checkout-step-2">
        <!-- step 5/6-->
        <div class="woocommerce-checkout-steps__data">
            <h5>Indirizzo email:</h5>
            <ul>
                <li>{{ email }}</li>
            </ul>

            <a class="woocommerce-checkout-steps__edit" href="#">Modifica <i class="fal fa-pencil"></i></a>
        </div>

        <h4>Indirizzo spedizione</h4>

        <form action="" class="checkout woocommerce-checkout" @submit.prevent="confirmFormData">
            <div class="woocommerce-billing-fields__field-wrapper">
                <div class="form-row form-row-wide">
                    <label for="country">Paese <span>*</span></label>
                    <select name="country" id="country" v-model="formData.country">
                        <option value="IT" selected>Italia</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="AF">Afghanistan</option>
                        <option value="AL">Albania</option>
                        <option value="DZ">Algeria</option>
                        <option value="AS">American Samoa</option>
                        <option value="AD">Andorra</option>
                        <option value="AO">Angola</option>
                        <option value="AI">Anguilla</option>
                        <option value="AQ">Antarctica</option>
                        <option value="AG">Antigua and Barbuda</option>
                        <option value="AR">Argentina</option>
                        <option value="AM">Armenia</option>
                        <option value="AW">Aruba</option>
                        <option value="AU">Australia</option>
                        <option value="AT">Austria</option>
                        <option value="AZ">Azerbaijan</option>
                        <option value="BS">Bahamas</option>
                        <option value="BH">Bahrain</option>
                        <option value="BD">Bangladesh</option>
                        <option value="BB">Barbados</option>
                        <option value="BY">Belarus</option>
                        <option value="BE">Belgium</option>
                        <option value="BZ">Belize</option>
                        <option value="BJ">Benin</option>
                        <option value="BM">Bermuda</option>
                        <option value="BT">Bhutan</option>
                        <option value="BO">Bolivia</option>
                        <option value="BA">Bosnia and Herzegovina</option>
                        <option value="BW">Botswana</option>
                        <option value="BV">Bouvet Island</option>
                        <option value="BR">Brazil</option>
                        <option value="IO">British Indian Ocean Territory</option>
                        <option value="BN">Brunei Darussalam</option>
                        <option value="BG">Bulgaria</option>
                        <option value="BF">Burkina Faso</option>
                        <option value="BI">Burundi</option>
                        <option value="KH">Cambodia</option>
                        <option value="CM">Cameroon</option>
                        <option value="CV">Cape Verde</option>
                        <option value="KY">Cayman Islands</option>
                        <option value="CF">Central African Republic</option>
                        <option value="TD">Chad</option>
                        <option value="CL">Chile</option>
                        <option value="CN">China</option>
                        <option value="CX">Christmas Island</option>
                        <option value="CC">Cocos (Keeling) Islands</option>
                        <option value="CO">Colombia</option>
                        <option value="KM">Comoros</option>
                        <option value="CG">Congo</option>
                        <option value="CD">Congo (Democratic Republic)</option>
                        <option value="CK">Cook Islands</option>
                        <option value="CR">Costa Rica</option>
                        <option value="HR">Croatia</option>
                        <option value="CU">Cuba</option>
                        <option value="CY">Cyprus</option>
                        <option value="CZ">Czech Republic</option>
                        <option value="DK">Denmark</option>
                        <option value="DJ">Djibouti</option>
                        <option value="DM">Dominica</option>
                        <option value="DO">Dominican Republic</option>
                        <option value="TP">East Timor</option>
                        <option value="EC">Ecuador</option>
                        <option value="EG">Egypt</option>
                        <option value="SV">El Salvador</option>
                        <option value="GQ">Equatorial Guinea</option>
                        <option value="ER">Eritrea</option>
                        <option value="EE">Estonia</option>
                        <option value="ET">Ethiopia</option>
                        <option value="FK">Falkland Islands</option>
                        <option value="FO">Faroe Islands</option>
                        <option value="FJ">Fiji</option>
                        <option value="FI">Finland</option>
                        <option value="FR">France</option>
                        <option value="FX">France (European Territory)</option>
                        <option value="GF">French Guiana</option>
                        <option value="TF">French Southern Territories</option>
                        <option value="GA">Gabon</option>
                        <option value="GM">Gambia</option>
                        <option value="GE">Georgia</option>
                        <option value="DE">Germany</option>
                        <option value="GH">Ghana</option>
                        <option value="GI">Gibraltar</option>
                        <option value="GR">Greece</option>
                        <option value="GL">Greenland</option>
                        <option value="GD">Grenada</option>
                        <option value="GP">Guadeloupe</option>
                        <option value="GU">Guam</option>
                        <option value="GT">Guatemala</option>
                        <option value="GN">Guinea</option>
                        <option value="GW">Guinea Bissau</option>
                        <option value="GY">Guyana</option>
                        <option value="HT">Haiti</option>
                        <option value="HM">Heard and McDonald Islands</option>
                        <option value="VA">Holy See (Vatican)</option>
                        <option value="HN">Honduras</option>
                        <option value="HK">Hong Kong</option>
                        <option value="HU">Hungary</option>
                        <option value="IS">Iceland</option>
                        <option value="IN">India</option>
                        <option value="ID">Indonesia</option>
                        <option value="IR">Iran</option>
                        <option value="IQ">Iraq</option>
                        <option value="IE">Ireland</option>
                        <option value="IL">Israel</option>
                        <option value="CI">Cote D&rsquo;Ivoire</option>
                        <option value="JM">Jamaica</option>
                        <option value="JP">Japan</option>
                        <option value="JO">Jordan</option>
                        <option value="KZ">Kazakhstan</option>
                        <option value="KE">Kenya</option>
                        <option value="KI">Kiribati</option>
                        <option value="KW">Kuwait</option>
                        <option value="KG">Kyrgyzstan</option>
                        <option value="LA">Laos</option>
                        <option value="LV">Latvia</option>
                        <option value="LB">Lebanon</option>
                        <option value="LS">Lesotho</option>
                        <option value="LR">Liberia</option>
                        <option value="LY">Libya</option>
                        <option value="LI">Liechtenstein</option>
                        <option value="LT">Lithuania</option>
                        <option value="LU">Luxembourg</option>
                        <option value="MO">Macau</option>
                        <option value="MK">Macedonia</option>
                        <option value="MG">Madagascar</option>
                        <option value="MW">Malawi</option>
                        <option value="MY">Malaysia</option>
                        <option value="MV">Maldives</option>
                        <option value="ML">Mali</option>
                        <option value="MT">Malta</option>
                        <option value="MH">Marshall Islands</option>
                        <option value="MQ">Martinique</option>
                        <option value="MR">Mauritania</option>
                        <option value="MU">Mauritius</option>
                        <option value="YT">Mayotte</option>
                        <option value="MX">Mexico</option>
                        <option value="FM">Micronesia</option>
                        <option value="MD">Moldova</option>
                        <option value="MC">Monaco</option>
                        <option value="MN">Mongolia</option>
                        <option value="ME">Montenegro</option>
                        <option value="MS">Montserrat</option>
                        <option value="MA">Morocco</option>
                        <option value="MZ">Mozambique</option>
                        <option value="MM">Myanmar</option>
                        <option value="NA">Namibia</option>
                        <option value="NR">Nauru</option>
                        <option value="NP">Nepal</option>
                        <option value="NL">Netherlands</option>
                        <option value="AN">Netherlands Antilles</option>
                        <option value="NC">New Caledonia</option>
                        <option value="NZ">New Zealand</option>
                        <option value="NI">Nicaragua</option>
                        <option value="NE">Niger</option>
                        <option value="NG">Nigeria</option>
                        <option value="NU">Niue</option>
                        <option value="NF">Norfolk Island</option>
                        <option value="KP">North Korea</option>
                        <option value="MP">Northern Mariana Islands</option>
                        <option value="NO">Norway</option>
                        <option value="OM">Oman</option>
                        <option value="PK">Pakistan</option>
                        <option value="PW">Palau</option>
                        <option value="PS">Palestinian Territory</option>
                        <option value="PA">Panama</option>
                        <option value="PG">Papua New Guinea</option>
                        <option value="PY">Paraguay</option>
                        <option value="PE">Peru</option>
                        <option value="PH">Philippines</option>
                        <option value="PN">Pitcairn</option>
                        <option value="PL">Poland</option>
                        <option value="PF">Polynesia</option>
                        <option value="PT">Portugal</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="QA">Qatar</option>
                        <option value="RE">Reunion</option>
                        <option value="RO">Romania</option>
                        <option value="RU">Russian Federation</option>
                        <option value="RW">Rwanda</option>
                        <option value="GS">S. Georgia &amp; S. Sandwich Isls.</option>
                        <option value="SH">Saint Helena</option>
                        <option value="KN">Saint Kitts &amp; Nevis Anguilla</option>
                        <option value="LC">Saint Lucia</option>
                        <option value="PM">Saint Pierre and Miquelon</option>
                        <option value="VC">Saint Vincent &amp; Grenadines</option>
                        <option value="WS">Samoa</option>
                        <option value="SM">San Marino</option>
                        <option value="ST">Sao Tome and Principe</option>
                        <option value="SA">Saudi Arabia</option>
                        <option value="SN">Senegal</option>
                        <option value="RS">Serbia</option>
                        <option value="SC">Seychelles</option>
                        <option value="SL">Sierra Leone</option>
                        <option value="SG">Singapore</option>
                        <option value="SK">Slovakia</option>
                        <option value="SI">Slovenia</option>
                        <option value="SB">Solomon Islands</option>
                        <option value="SO">Somalia</option>
                        <option value="ZA">South Africa</option>
                        <option value="KR">South Korea</option>
                        <option value="ES">Spain</option>
                        <option value="LK">Sri Lanka</option>
                        <option value="SD">Sudan</option>
                        <option value="SR">Suriname</option>
                        <option value="SZ">Swaziland</option>
                        <option value="SE">Sweden</option>
                        <option value="CH">Switzerland</option>
                        <option value="SY">Syrian Arab Republic</option>
                        <option value="TW">Taiwan</option>
                        <option value="TJ">Tajikistan</option>
                        <option value="TZ">Tanzania</option>
                        <option value="TH">Thailand</option>
                        <option value="TG">Togo</option>
                        <option value="TK">Tokelau</option>
                        <option value="TO">Tonga</option>
                        <option value="TT">Trinidad and Tobago</option>
                        <option value="TN">Tunisia</option>
                        <option value="TR">Turkey</option>
                        <option value="TM">Turkmenistan</option>
                        <option value="TC">Turks and Caicos Islands</option>
                        <option value="TV">Tuvalu</option>
                        <option value="UG">Uganda</option>
                        <option value="UA">Ukraine</option>
                        <option value="AE">United Arab Emirates</option>
                        <option value="GB">United Kingdom</option>
                        <option value="UY">Uruguay</option>
                        <option value="UM">USA Minor Outlying Islands</option>
                        <option value="UZ">Uzbekistan</option>
                        <option value="VU">Vanuatu</option>
                        <option value="VE">Venezuela</option>
                        <option value="VN">Vietnam</option>
                        <option value="VG">Virgin Islands (British)</option>
                        <option value="VI">Virgin Islands (USA)</option>
                        <option value="WF">Wallis and Futuna Islands</option>
                        <option value="EH">Western Sahara</option>
                        <option value="YE">Yemen</option>
                        <option value="ZR">Zaire</option>
                        <option value="ZM">Zambia</option>
                        <option value="ZW">Zimbabwe</option>
                    </select>
                </div>
                <div class="form-row form-row-wide">
                    <label for="address">Indirizzo <span>*</span></label>
                    <input type="text" placeholder="Via e numero civico" id="address" v-model="formData.address">
                </div>
                <div class="form-row form-row-third">
                    <label for="zip-code">Cap <span>*</span></label>
                    <input type="text" placeholder="Placeholder" id="zip-code" v-model="formData.postcode">
                </div>
                <div class="form-row form-row-third">
                    <label for="city">City <span>*</span></label>
                    <input type="text" placeholder="Placeholder" id="city" v-model="formData.city">
                </div>
                <div class="form-row form-row-third">
                    <label for="province">Provincia <span>*</span></label>
                    <select name="province" id="province" v-model="formData.state">
                        <option value="AG">Agrigento</option>
                        <option value="AL">Alessandria</option>
                        <option value="AN">Ancona</option>
                        <option value="AO">Aosta</option>
                        <option value="AR">Arezzo</option>
                        <option value="AP">Ascoli Piceno</option>
                        <option value="AT">Asti</option>
                        <option value="AV">Avellino</option>
                        <option value="BA">Bari</option>
                        <option value="BT">Barletta-Andria-Trani</option>
                        <option value="BL">Belluno</option>
                        <option value="BN">Benevento</option>
                        <option value="BG">Bergamo</option>
                        <option value="BI">Biella</option>
                        <option value="BO">Bologna</option>
                        <option value="BZ">Bolzano</option>
                        <option value="BS">Brescia</option>
                        <option value="BR">Brindisi</option>
                        <option value="CA">Cagliari</option>
                        <option value="CL">Caltanissetta</option>
                        <option value="CB">Campobasso</option>
                        <option value="CE">Caserta</option>
                        <option value="CT">Catania</option>
                        <option value="CZ">Catanzaro</option>
                        <option value="CH">Chieti</option>
                        <option value="CO">Como</option>
                        <option value="CS">Cosenza</option>
                        <option value="CR">Cremona</option>
                        <option value="KR">Crotone</option>
                        <option value="CN">Cuneo</option>
                        <option value="EN">Enna</option>
                        <option value="FM">Fermo</option>
                        <option value="FE">Ferrara</option>
                        <option value="FI">Firenze</option>
                        <option value="FG">Foggia</option>
                        <option value="FC">Forl&igrave;-Cesena</option>
                        <option value="FR">Frosinone</option>
                        <option value="GE">Genova</option>
                        <option value="GO">Gorizia</option>
                        <option value="GR">Grosseto</option>
                        <option value="IM">Imperia</option>
                        <option value="IS">Isernia</option>
                        <option value="AQ">L'aquila</option>
                        <option value="SP">La spezia</option>
                        <option value="LT">Latina</option>
                        <option value="LE">Lecce</option>
                        <option value="LC">Lecco</option>
                        <option value="LI">Livorno</option>
                        <option value="LO">Lodi</option>
                        <option value="LU">Lucca</option>
                        <option value="MC">Macerata</option>
                        <option value="MN">Mantova</option>
                        <option value="MS">Massa-Carrara</option>
                        <option value="MT">Matera</option>
                        <option value="ME">Messina</option>
                        <option value="MI">Milano</option>
                        <option value="MO">Modena</option>
                        <option value="MB">Monza e Brianza</option>
                        <option value="NA">Napoli</option>
                        <option value="NO">Novara</option>
                        <option value="NU">Nuoro</option>
                        <option value="OR">Oristano</option>
                        <option value="PD">Padova</option>
                        <option value="PA">Palermo</option>
                        <option value="PR">Parma</option>
                        <option value="PV">Pavia</option>
                        <option value="PG">Perugia</option>
                        <option value="PU">Pesaro e Urbino</option>
                        <option value="PE">Pescara</option>
                        <option value="PC">Piacenza</option>
                        <option value="PI">Pisa</option>
                        <option value="PT">Pistoia</option>
                        <option value="PN">Pordenone</option>
                        <option value="PZ">Potenza</option>
                        <option value="PO">Prato</option>
                        <option value="RG">Ragusa</option>
                        <option value="RA">Ravenna</option>
                        <option value="RC">Reggio Calabria</option>
                        <option value="RE">Reggio Emilia</option>
                        <option value="RI">Rieti</option>
                        <option value="RN">Rimini</option>
                        <option value="RM">Roma</option>
                        <option value="RO">Rovigo</option>
                        <option value="SA">Salerno</option>
                        <option value="SS">Sassari</option>
                        <option value="SV">Savona</option>
                        <option value="SI">Siena</option>
                        <option value="SR">Siracusa</option>
                        <option value="SO">Sondrio</option>
                        <option value="SU">Sud Sardegna</option>
                        <option value="TA">Taranto</option>
                        <option value="TE">Teramo</option>
                        <option value="TR">Terni</option>
                        <option value="TO">Torino</option>
                        <option value="TP">Trapani</option>
                        <option value="TN">Trento</option>
                        <option value="TV">Treviso</option>
                        <option value="TS">Trieste</option>
                        <option value="UD">Udine</option>
                        <option value="VA">Varese</option>
                        <option value="VE">Venezia</option>
                        <option value="VB">Verbano-Cusio-Ossola</option>
                        <option value="VC">Vercelli</option>
                        <option value="VR">Verona</option>
                        <option value="VV">Vibo valentia</option>
                        <option value="VI">Vicenza</option>
                        <option value="VT">Viterbo</option>
                    </select>
                </div>

                <div class="form-row form-row-wide">
                    <label for="notes">Note per la consegna</label>
                    <textarea id="notes" placeholder="Nome sul citofono quando diverso dall'intestatario, C/O, interno, richieste particolari" v-model="formData.notes"></textarea>
                </div>
            </div>
            <input type="submit" value="Vai al pagamento" class="btn btn--primary">
        </form>
    </section>
</template>