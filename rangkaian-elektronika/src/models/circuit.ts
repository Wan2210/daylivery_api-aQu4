export class Circuit {
    private components: any[];

    constructor() {
        this.components = [];
    }

    addComponent(component: any) {
        this.components.push(component);
    }

    removeComponent(component: any) {
        this.components = this.components.filter(c => c !== component);
    }

    getComponents() {
        return this.components;
    }
}