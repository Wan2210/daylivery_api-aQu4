export interface Component {
    id: string;
    type: string;
    value: number;
    connections: string[];
}

export interface SimulationResult {
    success: boolean;
    message: string;
    data: any;
}