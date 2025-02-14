import { beforeAll } from "bun:test";
import { window } from 'happy-dom';

beforeAll(() => {
  // Setup happy-dom
  global.window = window;
  global.document = window.document;
  global.navigator = window.navigator;
}); 