<script setup lang="ts">
import {onMounted, reactive, ref, toRaw} from "vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";

defineProps({
    email: String
});

const emit = defineEmits<{
    (e: 'profileDataSubmitted', formData: object): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const formData = reactive({
    firstName: '',
    lastName: '',
    birthDay: '',
    phone: '',
    createAccount: false,
    password: '',
    confirmPassword: ''
});

onMounted(() => {
    const profileData = checkoutDataStore.profileData;
    if(profileData.firstName !== ''){
        formData.firstName = profileData.firstName;
    }
    if(profileData.lastName !== ''){
        formData.lastName = profileData.lastName;
    }
    if(profileData.phone !== ''){
        formData.phone = profileData.phone;
    }
});

function confirmFormData(){
    emit('profileDataSubmitted', toRaw(formData));
}
</script>

<template>
  <div>
    <h4>Benvenuto!</h4>

    <div class="woocommerce-checkout-steps__data">
      <h5>Indirizzo email</h5>
      <ul>
        <li>{{ email }}</li>
      </ul>
    </div>

    <div class="checkout woocommerce-checkout">
      <div class="woocommerce-billing-fields__field-wrapper">
        <div class="form-row form-row-first">
          <label for="first-name">Nome <span>*</span></label>
          <input type="text" placeholder="Nome" id="first-name" v-model="formData.firstName">
        </div>
        <div class="form-row form-row-last">
          <label for="last-name">Cognome <span>*</span></label>
          <input type="text" placeholder="Cognome" id="last-name" v-model="formData.lastName">
        </div>
        <div class="form-row form-row-first">
          <label for="birth-date">Data di nascita <span>*</span></label>
          <input type="text" placeholder="GG/MM/AAAA" id="birth-date" v-model="formData.birthDay">
        </div>
        <div class="form-row form-row-last">
          <label for="phone">Telefono <span>*</span></label>
          <input type="tel" placeholder="+39" id="phone" v-model="formData.phone">
        </div>

        <div class="form-row form-row-wide" v-show="!checkoutDataStore.isLoggedIn">
          <input type="checkbox" id="save" v-model="formData.createAccount">
          <label for="save">Salva questi dati per il prossimo acquisto</label>
        </div>

        <div class="form-row form-row-wide" v-show="formData.createAccount">
          <label for="insert-password">Password</label>
          <input type="password" placeholder="Inserisci una password" id="insert-password" v-model="formData.password">
        </div>

        <div class="form-row form-row-wide" v-show="formData.createAccount">
          <label for="confirm-password">Conferma password</label>
          <input type="password" placeholder="Inserisci una password" id="confirm-password" v-model="formData.confirmPassword">
        </div>

        <div class="form-row form-row-wide" v-show="!checkoutDataStore.isLoggedIn">
          <h4>Rendi speciale la tua Shopping Experience!</h4>
          <h5>Perché registrarsi?</h5>
          <ul>
            <li>Puoi modificare i tuoi dati personali e aggiornare il tuo account per un’esperienza di acquisto più semplice e veloce</li>
            <li>Puoi visualizzare lo stato dei tuoi ordini</li>
            <li>Avrai accesso a Promo esclusive</li>
            <li>Sarai sempre il primo ad essere aggiornato sui nuovi prodotti</li>
            <li>Avrai sempre la spedizione gratuita per ordini superiori a 70 Euro</li>
          </ul>
        </div>
      </div>
      <input type="submit" value="Procedi" class="btn btn--primary" @click.prevent="confirmFormData">
    </div>
  </div>
</template>