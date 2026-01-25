import requests
import time
import random
import sys

# config:
BASE_URL = "http://localhost/mes/api" 
EXPORT_ENDPOINT = f"{BASE_URL}/machines-export.php"
RECEIVE_ENDPOINT = f"{BASE_URL}/wago-receive.php"

def get_machine_ids():
    """Fetches valid machine IDs from the MES API as CSV"""
    try:
        print(f"Fetching machine list from: {EXPORT_ENDPOINT}?format=csv")
        response = requests.get(f"{EXPORT_ENDPOINT}?format=csv")
        response.raise_for_status()
        
        csv_data = response.text.strip()
        if not csv_data:
            return []
            
        ids = [int(x) for x in csv_data.split(',')]
        print(f"Successfully loaded {len(ids)} machines: {ids}")
        return ids
    except Exception as e:
        print(f"Error fetching machines: {e}")
        return []

def simulate_plc_cycle(machine_ids):
    """Main simulation loop"""
    if not machine_ids:
        print("No machines available. Exiting.")
        return

    print("\n--- STARTING PLC SIMULATION (Press Ctrl+C to stop) ---")
    
    try:
        while True:
            machine_id = random.choice(machine_ids)
            
            count = random.randint(1, 5)
            
            payload = {
                'machine_id': machine_id,
                'production_count': count
            }
            
            try:
                print(f"Machine #{machine_id} reporting {count} parts...", end=" ")
                res = requests.post(RECEIVE_ENDPOINT, json=payload, timeout=2)
                
                if res.status_code == 200:
                    print(f"SUCCESS: {res.text}")
                else:
                    print(f"FAILED ({res.status_code}): {res.text}")
                    
            except requests.exceptions.RequestException as e:
                print(f"CONNECTION ERROR: {e}")

            time.sleep(random.uniform(0.5, 3.0))
            
    except KeyboardInterrupt:
        print("\nSimulation stopped by user.")

if __name__ == "__main__":
    ids = get_machine_ids()
    simulate_plc_cycle(ids)