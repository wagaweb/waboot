<div id="addNewComponents">
    <div class="componentsWrapper">
        <waboot-component v-for="component in available_components" :data="component" :key="component.slug" inline-template>
            <div>
                {{ data.title }}
            </div>
        </waboot-component>
    </div>
</div>