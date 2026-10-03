import { Simulator } from '../src/services/simulator';

describe('Simulator', () => {
    let simulator: Simulator;

    beforeEach(() => {
        simulator = new Simulator();
    });

    test('should run simulation and return results', () => {
        simulator.resetSimulation();
        const results = simulator.runSimulation();
        expect(results).toBeDefined();
        expect(Array.isArray(results)).toBe(true);
    });

    test('should reset simulation', () => {
        simulator.runSimulation();
        simulator.resetSimulation();
        const resultsAfterReset = simulator.getResults();
        expect(resultsAfterReset).toEqual([]);
    });

    test('should get results after running simulation', () => {
        simulator.resetSimulation();
        simulator.runSimulation();
        const results = simulator.getResults();
        expect(results).toBeDefined();
        expect(results.length).toBeGreaterThan(0);
    });
});