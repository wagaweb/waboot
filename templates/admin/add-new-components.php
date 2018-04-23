<?php global $page_title; $page_title = __('Add Components','waboot'); ?>

<?php require_once get_template_directory() . '/wbf/admin-header.php'; ?>

<div id="addNewComponents" class="admin-wrapper wrap">
    <div :class="{ loading: isLoadingComponents, loaded: !isLoadingComponents, componentsWrapper: true }">
        <waboot-component v-on:installed="componentInstalled" v-on:activated="componentActivated" v-for="component_data in available_components" :component_data="component_data" :key="component_data.slug" inline-template>
            <div class="component__card" :data-component="component_data.slug">
                <div class="component__inner">
                    <div class="component__thumbnail">
                        <img :src="component_data.thumbnail" class="component__image" />
                    </div>
                    <div class="component__info">
                        <h3>{{ component_data.title }}</h3>
                        <p>
                            {{ component_data.description }}
                        </p>
                        <p class="component__authors">
                            <cite>By </cite>{{ component_data.author }}
                        </p>
                    </div>
                    <div class="component__buttons">
                        <a href="#" class="component__button download-now__button" v-on:click.prevent="downloadComponent" :disabled="installing" data-install-button v-if="component_data.status === 0">{{ actionButtonLabel }}</a>
                        <a href="#" class="component__button activate-now__button" v-on:click.prevent="activateComponent" :disabled="activating" data-activate-button v-else-if="component_data.status === 1">{{ actionButtonLabel }}</a>
                        <a href="#" class="component__button activate-now__button" disabled="disable" v-on:click.prevent v-else>Active</a>
                    </div>
                </div>
            </div>
        </waboot-component>
    </div>
</div>