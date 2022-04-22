import {
  LocaleMessageDictionary,
  VueMessageType
} from 'vue-i18n';
import enGB from './en-GB';
import itIT from './it-IT';

export interface MessageDictionarySchema
  extends LocaleMessageDictionary<VueMessageType> {
	price: string;
	apply: string;
	filterFor: string;
	default: string;
	alphabetic: string;
	popularity: string;
	priceHighToLow: string;
	priceLowToHigh: string;
	showMore: string;
	noProductsFound: string;
	from: string;
	outOfStock: string;
	addToCart: string
	addProductToCart: string;
    showProduct: string;
}

export enum AvailableLanguages {
  itIT = 'it-IT',
  enGB = 'en-GB',
}

export const messages: Record<AvailableLanguages, MessageDictionarySchema> = {
  'it-IT': itIT,
  'en-GB': enGB,
};

export default messages;
