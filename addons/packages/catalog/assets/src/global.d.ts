// scss modules
declare module '*.scss' {
  const content: string;
  export default content;
}

// global gtag function
declare const gtag: ((...args: any) => any) | undefined;

// global analytics v4 object
declare var dataLayer: any;

// global JWM WooCommerce Wishlist function
declare var JVMWooCommerceWishlist: {
  build(): void;
} | undefined;
