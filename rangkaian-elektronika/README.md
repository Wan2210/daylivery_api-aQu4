# Rangkaian Elektronika

## Overview
Rangkaian Elektronika is a TypeScript-based application designed for creating, editing, and simulating electronic circuits. This project provides a user-friendly interface for users to design circuits and run simulations to analyze their behavior.

## Project Structure
```
rangkaian-elektronika
├── src
│   ├── app.ts                # Entry point of the application
│   ├── components
│   │   └── circuit-editor.ts  # Circuit editor component
│   ├── models
│   │   └── circuit.ts         # Circuit model
│   ├── services
│   │   └── simulator.ts       # Circuit simulation service
│   └── types
│       └── index.ts          # Type definitions
├── tests
│   └── simulator.test.ts      # Unit tests for the simulator
├── package.json               # NPM package configuration
├── tsconfig.json              # TypeScript configuration
└── README.md                  # Project documentation
```

## Setup Instructions
1. Clone the repository:
   ```
   git clone https://github.com/yourusername/rangkaian-elektronika.git
   ```
2. Navigate to the project directory:
   ```
   cd rangkaian-elektronika
   ```
3. Install the dependencies:
   ```
   npm install
   ```

## Usage
To run the application, execute the following command:
```
npm start
```

## Examples
- Create a new circuit using the CircuitEditor component.
- Add components to the circuit and simulate its behavior using the Simulator service.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License.