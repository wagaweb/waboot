<?php global $page_title; $page_title = __('Add Components','waboot'); ?>

<?php require_once get_template_directory() . '/wbf/admin-header.php'; ?>

<div id="addNewComponents">
    <div :class="{ loading: isLoadingComponents, loaded: !isLoadingComponents, componentsWrapper: true }">
        <waboot-component v-on:installed="componentInstalled" v-on:activated="componentActivated" v-for="component_data in available_components" :component_data="component_data" :key="component_data.slug" inline-template>
            <div class="component-card" :data-component="component_data.slug">
                <div class="component-card-top">
                    <div class="thumbnail column-thumbnail">
                        <img :src="component_data.thumbnail" class="component-icon" />
                    </div>
                    <div class="desc column-description">
                        <h3>{{ component_data.title }}</h3>
                        <p>
                            {{ component_data.description }}
                        </p>
                        <p class="authors">
                            <cite>By </cite>{{ component_data.author }}
                        </p>
                    </div>
                    <div class="action-links">
                        <ul class="component-action-buttons">
                            <li>
                                <a href="#" class="button download-now" v-on:click.prevent="downloadComponent" :disabled="installing" data-install-button v-if="component_data.status === 0">{{ actionButtonLabel }}</a>
                                <a href="#" class="button activate-now" v-on:click.prevent="activateComponent" data-activate-button v-else-if="component_data.status === 1">{{ actionButtonLabel }}</a>
                                <a href="#" class="button activate-now" v-on:click.prevent v-else>Active</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="component-card-bottom">

                </div>
            </div>
        </waboot-component>
    </div>
</div>