import { CircuitEditor } from './components/circuit-editor';
import { Simulator } from './services/simulator';

class App {
    private circuitEditor: CircuitEditor;
    private simulator: Simulator;

    constructor() {
        this.circuitEditor = new CircuitEditor();
        this.simulator = new Simulator();
    }

    public initialize(): void {
        this.setupEventListeners();
        this.render();
    }

    private setupEventListeners(): void {
        // Setup event listeners for user interactions
    }

    private render(): void {
        // Render the main application interface
        this.circuitEditor.render();
    }

    public run(): void {
        this.initialize();
    }
}

const app = new App();
app.run();