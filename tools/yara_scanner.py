import yara
import sys
import os
import json
import glob

def scan_file(file_path, rules_dir):
    try:
        # 1. Compile all rules in the directory
        filepaths = glob.glob(os.path.join(rules_dir, '*.yar'))
        
        if not filepaths:
            return {'status': 'error', 'message': 'No YARA rules found.'}

        rules = yara.compile(filepaths={os.path.basename(f): f for f in filepaths})

        # 2. Scan the file
        matches = rules.match(file_path)

        # 3. Format results
        results = []
        for match in matches:
            safe_strings = []
            for s in match.strings:
                if hasattr(s, 'identifier'):
                    # yara-python >= 4.3.0 uses StringMatch objects
                    safe_instances = []
                    for i in s.instances:
                        safe_instances.append({
                            'offset': getattr(i, 'offset', 0),
                            'matched_length': getattr(i, 'matched_length', 0),
                            'matched_data': i.matched_data.hex() if hasattr(i.matched_data, 'hex') else str(getattr(i, 'matched_data', ''))
                        })
                    safe_strings.append({
                        'identifier': s.identifier,
                        'instances': safe_instances
                    })
                else:
                    # older yara-python versions returned tuples
                    try:
                        safe_strings.append({
                            'offset': s[0],
                            'identifier': s[1],
                            'matched_data': s[2].hex() if hasattr(s[2], 'hex') else str(s[2])
                        })
                    except:
                        safe_strings.append(str(s))

            results.append({
                'rule': match.rule,
                'namespace': match.namespace,
                'tags': match.tags,
                'meta': match.meta,
                'strings': safe_strings
            })

        return {'status': 'success', 'matches': results, 'match_count': len(results)}

    except yara.Error as e:
        return {'status': 'error', 'message': f'YARA Error: {str(e)}'}
    except Exception as e:
        return {'status': 'error', 'message': str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({'status': 'error', 'message': 'Usage: python yara_scanner.py <file_path> <rules_dir>'}))
        sys.exit(1)

    file_to_scan = sys.argv[1]
    rules_directory = sys.argv[2]

    if not os.path.exists(file_to_scan):
        print(json.dumps({'status': 'error', 'message': f'File not found: {file_to_scan}'}))
        sys.exit(1)

    if not os.path.exists(rules_directory):
        print(json.dumps({'status': 'error', 'message': f'Rules directory not found: {rules_directory}'}))
        sys.exit(1)
        
    result = scan_file(file_to_scan, rules_directory)
    print(json.dumps(result))
