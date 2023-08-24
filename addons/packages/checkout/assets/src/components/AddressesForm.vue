<script setup lang="ts">
import {reactive, ref, toRaw, onMounted} from "vue";
import type { Ref } from 'vue'
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {WooCommerce} from "@/services/wp/woocommerce";
import type {fetchedCountry} from "../../env";

defineProps({
    email: String
});

const emit = defineEmits<{
    (e: 'AddressDataSubmitted', formData: object): void
    (e: 'countryChanged', formData: object): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const loadingCountries = ref(false);
const fetchedCountries: Ref<fetchedCountry[]> = ref([]);
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
    loadingCountries.value = true;
    new WooCommerce().fetchCountries().then((countries) => {
        loadingCountries.value = false;
        const rawCountries = toRaw(countries);
        const shippingCountries = rawCountries.shipping_countries;
        console.log(shippingCountries);
        fetchedCountries.value = shippingCountries;
        if(shippingData.country !== ''){
            formData.country = shippingData.country;
        }
    }).catch((error) => {
        loadingCountries.value = false;
        console.log('App.vue onMounted() error');
        console.error(error);
    });
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

function onCountryChange(){
    emit('countryChanged', toRaw(formData));
}

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

        <div class="checkout woocommerce-checkout">
            <div class="woocommerce-billing-fields__field-wrapper">
                <div class="form-row form-row-wide">
                    <label for="country">Paese <span>*</span></label>
                    <select name="country" id="country" v-model="formData.country" @change="onCountryChange" :disabled="loadingCountries">
                        <option v-for="country in fetchedCountries" :value="country.slug">{{ country.label }}</option>
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
            <input type="submit" value="Vai al pagamento" class="btn btn--primary" @click.prevent="confirmFormData">
        </div>
    </section>
</template>