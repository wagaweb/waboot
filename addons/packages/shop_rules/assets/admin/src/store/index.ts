import { InjectionKey } from 'vue'
import { createStore, useStore as baseUseStore, Store } from 'vuex'

// define your typings for the store state
export interface State {
    currentView: string,
    editingShopRuleId: number
}

// define injection key
export const key: InjectionKey<Store<State>> = Symbol()

export const store = createStore<State>({
    state () {
        return {
            currentView: 'list',
            editingShopRuleId: 0,
        }
    },
    mutations: {
        goToListView (state: State) {
            state.currentView = 'list';
            state.editingShopRuleId = 0;
        },
        goToNewView (state: State) {
            state.currentView = 'new';
            state.editingShopRuleId = 0;
        },
        goToEditView (state: State, id: number) {
            state.currentView = 'edit';
            state.editingShopRuleId = id;
        }
    }
})

// define your own `useStore` composition function
export function useStore () {
    return baseUseStore(key)
}