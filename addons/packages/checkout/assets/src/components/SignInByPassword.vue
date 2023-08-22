<template>
  <div>
    <h4>Bentornato!</h4>

    <div class="woocommerce-checkout-steps__data">
      <h5>Indirizzo email</h5>
      <ul class="">
        <li>federica@waga.it</li>
      </ul>
    </div>

    <form action="" class="checkout woocommerce-checkout">
      <div class="woocommerce-billing-fields__field-wrapper">
        <div class="form-row form-row-wide">
          <label for="password">Inserisci la tua password per accedere</label>
          <input type="password" placeholder="Password" id="password">
        </div>
        <div class="form-row form-row-wide">
          <a href="#">Password dimenticata?</a>
        </div>
      </div>
      <input type="submit" value="Accedi" class="btn btn--primary">
    </form>
  </div>
</template>