<template>
  <div>
    <h4>Benvenuto!</h4>

    <form action="" class="checkout woocommerce-checkout">
      <div class="woocommerce-billing-fields__field-wrapper">
        <div class="form-row form-row-wide">
          <label for="email">Inserisci la tua email prima di procedere</label>
          <input type="email" placeholder="Email" id="email">
        </div>
        <div class="form-row form-row-wide">
          <input type="checkbox" id="newsletter">
          <label for="newsletter">Ricevi offerte e comunicazioni</label>
        </div>
      </div>
      <input type="submit" value="Continua" class="btn btn--primary">
    </form>

    <h4>Oppure accedi con</h4>

    <p>Scegli un account social per accedere o registrati</p>

    [SOCIAL LOGIN BUTTONS HERE...]
  </div>
</template>